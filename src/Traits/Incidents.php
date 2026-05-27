<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use Carbon\Carbon;
use FredBradley\EasyTime\EasySeconds;
use FredBradley\TOPDesk\Exceptions\OperatorGroupNotFound;
use FredBradley\TOPDesk\Exceptions\OperatorNotFound;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;

/**
 * Trait Incidents.
 */
trait Incidents
{
    public function getOpenIncidentsByOperatorGroupId(string $operatorGroupId, string $processingStatus = 'Open', bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('open_incidents_'.$operatorGroupId.$processingStatus, $forgetCache);

        $processingStatusOperator = '==';
        if ($processingStatus === 'Open') {
            $processingStatusOperator = '!=';
            $processingStatus = 'Closed';
        }

        return Cache::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupId, $processingStatus, $processingStatusOperator) {
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
     * @param  array  $options  Keys: start, page_size, query (FIQL), fields, sort, etc.
     *
     * @throws RequestException|ConnectionException
     */
    public function getListOfIncidents(array $options = []): array|object
    {
        return $this->get('api/incidents', array_merge(['start' => 0, 'page_size' => 100], $options));
    }

    /**
     * @param  array  $data  Any incident fields: briefDescription, request, action, category{id},
     *                       subcategory{id}, operator{id}, operatorGroup{id}, processingStatus{id},
     *                       priority{id}, impact{id}, urgency{id}, duration{id}, targetDate,
     *                       onHold, closed, closedDate, closureCode{id}, costs, etc.
     *
     * @throws RequestException
     */
    public function updateIncident(string $id, array $data): object
    {
        return $this->patch('api/incidents/id/'.$id, $data);
    }

    /**
     * @throws RequestException
     */
    public function updateIncidentByNumber(string $number, array $data): object
    {
        return $this->patch('api/incidents/number/'.$number, $data);
    }

    /**
     * @throws RequestException|ConnectionException
     *
     * @deprecated use getIncident() instead
     */
    public function getIncidentbyNumber(string $topdeskIncidentNumber): object
    {
        return $this->getIncident($topdeskIncidentNumber);
    }

    /**
     * @param  string  $topdeskIncidentNumber  either the UNID or Ticket Number
     *
     * @throws RequestException|ConnectionException
     */
    public function getIncident(string $topdeskIncidentNumber): object
    {
        if (Str::isUuid($topdeskIncidentNumber)) {
            return $this->get('api/incidents/id/'.$topdeskIncidentNumber);
        }

        return $this->get('api/incidents/number/'.$topdeskIncidentNumber);
    }

    public function createNewFrom(string $topdeskIncidentNumber)
    {
        $incident = $this->getIncident($topdeskIncidentNumber);
        $unsets = ['id', 'number', 'asset', 'externalLinks', 'timeSpent', 'requests', 'caller'];
        $incident['callerLookup']['id'] = $incident['caller']['id'];

        foreach ($unsets as $unset) {
            unset($incident[$unset]);
        }
        $incident['category'] =
            [
                'id' => $incident['category']['id'],
            ];
        unset($incident['subcategory']['name']);

        dd($incident);

        $result = $this->createIncident($incident);

        return $result;
    }

    /**
     * @throws RequestException
     */
    public function createIncident(array $options): object
    {
        return $this->post('api/incidents', $options);
    }

    public function getOperatorByUsername(string $username, bool $forgetCache = false): Collection|\stdClass
    {
        $cacheKey = $this->setupCacheObject('operator_'.$username, $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::months(1), function () use ($username) {
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
     * @throws FrameworkNotDetected
     */
    public function getOperatorGroupId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('get_operator_group_name_'.$name, $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::months(1), function () use ($name) {
            $result = self::query()->get('api/operatorgroups/lookup', [
                'name' => $name,
                'archived' => false,
            ])->throw();

            $collection = collect($result->object()->results)->first();
            if (! is_null($collection)) {
                return $collection->id;
            }

            throw new OperatorGroupNotFound('Could not find Operator Group: '.$name);
        });
    }

    public function deprecatedgetOpenIncidentsByOperatorGroupId(string $operatorGroupId, ?string $processingStatus = null, array $fields = []): array
    {
        $queries = [
            'operatorGroup.id=='.$operatorGroupId,
        ];
        if (is_null($processingStatus)) {
            $queries[] = 'closed==false';
        } else {
            $queries[] = 'processingStatus.name=="'.$processingStatus.'"';
        }

        $customFieldsList = empty($fields) ? null : implode(',', $fields);
        $result = $this->get('api/incidents', [
            'pageSize' => 10,
            'query' => implode(';', $queries),
            'fields' => $customFieldsList,
        ]);

        return $result;
    }

    public function getProcessingStatusId(string $name, bool $forgetCache = false): string
    {
        $cacheKey = $this->setupCacheObject('getProcessingStatusId_'.$name, $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), function () use ($name, $forgetCache) {
            return $this->getProcessingStatus($name, $forgetCache)['id'];
        });
    }

    /**
     * @return \stdClass
     *
     * @throws ItemNotFoundException
     */
    public function getProcessingStatus(string $name, bool $forgetCache = false): array
    {
        $cacheKey = $this->setupCacheObject('status_'.$name, $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::weeks(1), function () use ($name, $forgetCache) {
            $statuses = $this->getAllProcessingStatuses($forgetCache);

            return $statuses->where('name', $name)->first() ?? throw new \Exception('Status Not Found');
        });
    }

    public function getAllProcessingStatuses(bool $forgetCache = false): Collection
    {
        $cacheKey = $this->setupCacheObject('statuses', $forgetCache);

        return Cache::remember($cacheKey, EasySeconds::days(30), function () {
            return self::query()->get('api/incidents/statuses')->collect();
        });
    }
}
