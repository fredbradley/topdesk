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
            return $this->getNumIncidents([
                'operatorGroup.id' => $this->getOperatorGroupId($operatorGroupName),
                'creation_date_start' => now()->format('Y-m-d'),
            ]);
        });
    }

    /**
     * @return int
     */
    public function countOpenTickets(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents([
                'operatorGroup.id' => $this->getOperatorGroupId($operatorGroupName),
                'fields' => 'id',
                'resolved' => 'false',
            ]);
        });
    }

    /**
     * @return int
     */
    public function countTicketsDueThisWeek(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents([
                'operatorGroup.id' => $this->getOperatorGroupId($operatorGroupName),
                'resolved' => 'false',
                'target_date_end' => now()->endOfWeek()->format('Y-m-d'),
            ]);
        });
    }

    /**
     * @return int
     */
    public function countBreachedTickets(string $operatorGroupName = 'I.T. Services'): int
    {
        $cacheKey = Str::slug(__METHOD__.$operatorGroupName);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function ($operatorGroupName) {
            return $this->getNumIncidents([
                'operatorGroup.id' => $this->getOperatorGroupId($operatorGroupName),
                'resolved' => 'false',
                'target_date_end' => now()->format('Y-m-d'),
            ]);
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
                return $this->getNumIncidents([
                    'operatorGroup.id' => $this->getOperatorGroupId($operatorGroupName),
                    'processing_status' => $processingStatusId,
                ]);
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
    public function getNumIncidents(array $options = []): int
    {
        return count($this->getIncidents($options));
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
        $incidents = Cacher::remember(
            'incidentsResolvedByOperatorAndTime_'.$operatorId.$timeString,
            EasySeconds::minutes(5),
            function () use ($operatorId, $timeString) {
                return $this->getNumIncidents([
                    'operator' => $operatorId,
                    'resolved' => 'true',
                    'closed_date_start' => now()->startOf($timeString)->format('Y-m-d'),
                ]);
            }
        );

        $changes = count($this->resolvedChangeActivitiesByOperatorIdByTime($operatorId, $timeString));

        return $incidents + $changes;
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
                return $this->getNumIncidents([
                    'operator' => $operatorId,
                    'resolved' => 'false',
                ]);
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
        $incidents = Cacher::remember(
            'countActiveIncidentsByOperatorID_'.$operatorId,
            EasySeconds::minutes(5),
            function () use ($operatorId) {
                return $this->getNumIncidents([
                    'operator' => $operatorId,
                    'resolved' => 'false',
                    'processing_status' => [
                        $this->getProcessingStatusId('Logged'),
                        $this->getProcessingStatusId('In progress'),
                        $this->getProcessingStatusId('Updated by user'),
                    ],
                ]);
            }
        );

        return $incidents + $this->countWaitingChangeActivitiesByOperatorId($operatorId);
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
            return $this->getNumIncidents([
                'operator' => $this->getOperatorGroupId($operatorGroup),
                'resolved' => 'false',
            ]);
        });
    }
}
