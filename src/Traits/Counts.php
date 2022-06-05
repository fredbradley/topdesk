<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\TransferStats;

trait Counts
{
    /**
     * @return int
     */
    public function countTicketsLoggedtoday(): int
    {
        return Cacher::remember('ticketsLoggedToday', EasySeconds::minutes(15), function () {
            return $this->getNumIncidents([
                'creation_date_start' => now()->format('Y-m-d'),
            ]);
        });
    }

    /**
     * @return int
     */
    public function countOpenTickets(string $operatorGroupName = 'Facilities', bool $forgetCache = false): int
    {
        $cacheKey = $this->setupCacheObject('openTickets_'.$operatorGroupName, $forgetCache);

        return Cacher::remember($cacheKey, EasySeconds::minutes(5), function () use ($operatorGroupName) {
            return $this->getNumIncidents([
                'operator' => self::getOperatorGroupId($operatorGroupName),
                'fields' => 'id',
                'resolved' => 'false',
            ]);
        });
    }

    /**
     * @return int
     */
    public function countTicketsDueThisWeek(): int
    {
        return Cacher::remember('ticketsDueThisWeek', EasySeconds::minutes(5), function () {
            return $this->getNumIncidents([
                'resolved' => 'false',
                'target_date_end' => now()->endOfWeek()->format('Y-m-d'),
            ]);
        });
    }

    /**
     * @return int
     */
    public function countBreachedTickets(): int
    {
        return Cacher::remember('ticketsBreached', EasySeconds::minutes(5), function () {
            return $this->getNumIncidents([
                'resolved' => 'false',
                'target_date_end' => now()->format('Y-m-d'),
            ]);
        });
    }

    /**
     * @param  string  $processingStatusId
     * @return int
     */
    public function countByProcessingStatusId(string $processingStatusId): int
    {
        return Cacher::remember(
            'countByStatusId_'.$processingStatusId,
            EasySeconds::minutes(5),
            function () use ($processingStatusId) {
                return $this->getNumIncidents([
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
        $response = self::query()->get('api/incidents', [
            'page_size' => 100,
            'start' => 0,
            'query' => array_merge([
                'start' => 0,
                'page_size' => 10000,
            ], $options),
        ])->throw();

        if ($response->status() === 204) {
            return 0;
        }

        return $response->collect()->pluck('operatorGroup');
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

        return json_decode((string) $response->getBody(), true);
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

        $changes = count($this->resolvedChangeActivitiesByOperatorIdByTime($operatorId, $timeString)['results']);

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
        return Cacher::setAndGet(
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
        return Cacher::setAndGet(
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
    public function countUnassignedTickets(): int
    {
        return Cacher::setAndGet('countUnassignedITTickets', EasySeconds::minutes(5), function () {
            return $this->getNumIncidents([
                'operator' => $this->getOperatorGroupId('I.T. Services'),
                'resolved' => 'false',
            ]);
        });
    }
}
