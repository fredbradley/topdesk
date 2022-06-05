<?php

namespace FredBradley\TOPDesk\Traits;

use Carbon\Carbon;
use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;
use FredBradley\TOPDesk\Exceptions\OperatorNotFound;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;

/**
 * Trait Incidents.
 */
trait Incidents
{
    /**
     * @param  string  $operatorGroupId
     * @param  string  $processingStatus
     * @param  bool  $forgetCache
     * @return \Illuminate\Support\Collection
     *
     * @throws \FredBradley\Cacher\Exceptions\FrameworkNotDetected
     */
    public function getOpenIncidentsByOperatorGroupId(string $operatorGroupId, string $processingStatus = 'Open', bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('open_incidents_'.$operatorGroupId.$processingStatus, $forgetCache);

        $processingStatusOperator = '==';
        if ($processingStatus === 'Open') {
            $processingStatusOperator = '!=';
            $processingStatus = 'Closed';
        }

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupId, $processingStatus, $processingStatusOperator) {
            try {
                $processingStatusId = $this->getProcessingStatusId($processingStatus);
                $response = self::query()->get('api/incidents', [
                    'start' => 0,
                    'page_size' => 10000,
                    'query' => '(operatorGroup.id=='.$operatorGroupId.');(processingStatus.id'.$processingStatusOperator.$processingStatusId.')',
                ])->throw()->object();

                return collect($response)->map(function ($ticket) {
                    $ticket->creationDate = Carbon::parse($ticket->creationDate);
                    if (! is_null($ticket->targetDate)) {
                        $ticket->targetDate = Carbon::parse($ticket->targetDate);
                    }

                    return $ticket;
                });
            } catch (RequestException $exception) {
                dd($exception->getMessage());
            }
        });
    }

    /**
     * @param  string  $username
     * @return \Illuminate\Support\Collection|\stdClass
     */
    public function getOperatorByUsername(string $username, $forgetCache = false): Collection|\stdClass
    {
        $cacheKey = $this->setupCacheObject('operator_'.$username, $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::months(1), function () use ($username) {
            $result = self::query()->get('api/operators', [
                'page_size' => 1,
                'query' => '(networkLoginName=='.$username.')',
            ]);

            if ($result->successful()) {
                if (is_null($result->object())) {
                    throw new OperatorNotFound('Could not find an operator with the username: '.$username, 422);
                }

                $finalResult = $result->collect();
                if (count($finalResult) === 1) {
                    return (object) $finalResult->first();
                }

                return $finalResult;
            }
        });
    }

    /**
     * @param  string  $name
     * @return string
     */
    public function getOperatorGroupId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('get_operator_group_name_'.$name, $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::months(1), function () use ($name) {
            $result = self::query()->get('api/operatorgroups/lookup', [
                'name' => $name,
                'archived' => false,
            ])->throw();

            $collection = collect($result->object()->results)->first();
            if (! is_null($collection)) {
                return $collection->id;
            }

            throw new \Exception('Could not find Operator Group: '.$name);
        });
    }

    /**
     * @param  string  $name
     * @return string
     */
    public function getProcessingStatusId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('getProcessingStatusId_'.$name, $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::weeks(1), function () use ($name, $forgetCache) {
            return $this->getProcessingStatus($name, $forgetCache)['id'];
        });
    }

    /**
     * @param  string  $name
     * @return array
     */
    public function getProcessingStatus(string $name, bool $forgetCache = false): array
    {
        $cacheKey = $this->setupCacheObject('status_'.$name, $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::weeks(1), function () use ($name, $forgetCache) {
            $statuses = $this->getAllProcessingStatuses($forgetCache);

            return $statuses->where('name', $name)->first() ?? throw new \Exception('Status Not Found');
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getAllProcessingStatuses(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('statuses', $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::days(30), function () {
            return self::query()->get('api/incidents/statuses')->collect();
        });
    }
}
