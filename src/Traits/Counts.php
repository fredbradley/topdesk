<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Str;

trait Counts
{
    /**
     * @return int
     */
    public function countTicketsLoggedtoday(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(15), function () use ($operatorGroupName) {
            return $this->getNumIncidents('operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName).';creationDate=gt='.now()->startOfDay()->toIso8601String());
        });
    }

    /**
     * @return int
     */
    public function countOpenTickets(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents('closed===false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName));
        });
    }

    /**
     * @return int
     */
    public function countTicketsDueThisWeek(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents('targetDate=lt='.now()->endOfWeek()->toIso8601String().';closed==false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName));
        });
    }

    /**
     * @return int
     */
    public function countBreachedTickets(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents('targetDate=gt='.now()->toIso8601String().';closed==false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName));
        });
    }

    /**
     * @param  string  $processingStatusId
     * @return int
     */
    public function countByProcessingStatusId(string $processingStatusId, string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$processingStatusId.$operatorGroupName);

        return Cacher::remember(
            $cacheKey,
            EasySeconds::minutes(5),
            function () use ($processingStatusId, $operatorGroupName) {
                return $this->getNumIncidents('closed==false;operatorGroup.id=='.$this->getOperatorGroupId($operatorGroupName).';processingStatus.id=='.$processingStatusId);
            }
        );
    }

    /**
     * @param  array  $firstArray
     * @param  array  $mergeFrom
     * @return string
     */
    private function convertArrayMergeToQueryString(array $firstArray, array $mergeFrom): string
    {
        $str = '';
        foreach ([$firstArray, $mergeFrom] as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        $str .= $key.'='.$subvalue.'&';
                    }
                } else {
                    $str .= $key.'='.$value.'&';
                }
            }
        }

        return $str;
    }

    /**
     * @param  array  $options
     * @return int
     */
    public function getNumIncidents(string $fiql, array $options = []): int
    {
        $response = $this->get('api/incidents', array_merge([
            'pageSize' => 10000, // the maximum
            'fields' => 'id', // limit it right down if we only care about numbers
            'query' => $fiql
        ], $options));
        return collect($response)->count();
    }

    /**
     * @param  array  $options
     * @return array
     */
    public function getIncidents(array $options = []): array
    {
        try {
            $response = $this->client->request('GET', 'api/incidents', [
                'query' => $this->convertArrayMergeToQueryString([
                    'start' => 0,
                    'page_size' => 10000,
                ], $options),
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                },
            ]);

            if ($response->getStatusCode() === 204) {
                return [];
            }
        } catch (ConnectException $exception) {
            return false;
        }

        return json_decode((string) $response->getBody(), false);
    }

    /**
     * @param  string  $operatorId
     * @param  string  $timeString
     * @return int
     */
    public function countResolvesByTime(string $operatorId, string $timeString = 'week'): int
    {
        return Cacher::remember(
            'incidentsResolvedByOperatorAndTime_'.$operatorId.$timeString,
            EasySeconds::minutes(5),
            function () use ($operatorId, $timeString) {
                return $this->getNumIncidents('operator.id=='.$operatorId.';closed==true;closedDate=gt='.now()->startOf($timeString)->toIso8601String());
            }
        );

    }

    /**
     * @param  string  $operatorId
     * @return mixed
     */
    public function countOpenTicketsByOperator(string $operatorId): int
    {
        $incidents = Cacher::remember(
            'countOpenTicketsByOperator_'.$operatorId,
            EasySeconds::minutes(5),
            function () use ($operatorId) {
                return $this->getNumIncidents('operator.id=='.$operatorId.';closed==false');
            }
        );

        return $incidents + $this->countWaitingChangeActivitiesByOperatorId($operatorId);
    }

    /**
     * @param  string  $operatorId
     * @return int
     */
    public function countActiveTicketsbyOperator(string $operatorId): int
    {
        return Cacher::remember(
            'countActiveIncidentsByOperatorID_'.$operatorId,
            EasySeconds::minutes(5),
            function () use ($operatorId) {
                return $this->getNumIncidents('operator.id=='.$operatorId.';closed==false;processingStatus.id=in=('.$this->getProcessingStatusId('Logged').','.$this->getProcessingStatusId('In progress').','.$this->getProcessingStatusId('Updated by user').')');
            }
        );

    }

    /**
     * @param  string  $operatorId
     * @return int
     */
    public function countWaitingChangeActivitiesByOperatorId(string $operatorId): int
    {
        return Cacher::remember(
            'countWaitingChangeActivitiesByOperator_'.$operatorId,
            EasySeconds::minutes(5),
            function () use ($operatorId) {
                return count($this->waitingChangeActivitiesByOperatorId($operatorId));
            }
        );
    }

    /**
     * @param  string  $statusName
     * @return int
     */
    public function countTicketsByStatus(string $statusName): int
    {
        return Cacher::remember(
            'countTicketsByStatus_'.$statusName,
            EasySeconds::minutes(5),
            function () use ($statusName) {
                $statusId = $this->getProcessingStatusId($statusName);

                return $this->countByProcessingStatusId($statusId);
            }
        );
    }

    /**
     * @return int
     */
    public function countUnassignedTickets(string $operatorGroup = 'I.T. Services'): int
    {
        return Cacher::remember('countUnassignedITTickets'.$operatorGroup, EasySeconds::minutes(5), function () use ($operatorGroup) {
            return $this->getNumIncidents('operator.id=='.$this->getOperatorGroupId($operatorGroup).';closed==false');
        });
    }
}
