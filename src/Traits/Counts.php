<?php

namespace FredBradley\TOPDesk\Traits;

trait Counts
{
    /**
     * @return int
     */
    public function countOpenTickets(): int
    {
        return $this->getNumIncidents([
            'resolved' => 'false',
        ]);
    }

    /**
     * @return int
     */
    public function countTicketsDueThisWeek(): int
    {
        return $this->getNumIncidents([
            'resolved' => 'false',
            'target_date_end' => now()->endOfWeek()->format('Y-m-d'),
        ]);
    }

    /**
     * @return int
     */
    public function countBreachedTickets(): int
    {
        return $this->getNumIncidents([
            'resolved' => 'false',
            'target_date_end' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * @param string $processingStatusId
     *
     * @return int
     */
    public function countByProcessingStatusId(string $processingStatusId): int
    {
        return $this->getNumIncidents([
            'processing_status' => $processingStatusId,
        ]);
    }

    /**
     * @param array $options
     *
     * @return int
     */
    public function getNumIncidents(array $options = []): int
    {
        $response = $this->client->request('GET', 'api/incidents', [
            'query' => array_merge([
                'start' => 0,
                'page_size' => 10000,
            ], $options),
        ]);
        if ($response->getStatusCode() === 204) {
            return 0;
        }

        return count(json_decode((string) $response->getBody(), true));
    }

    /**
     * @param string $operatorId
     * @param string $timeString
     *
     * @return int
     */
    public function countResolvesByTime(string $operatorId, string $timeString = 'week'): int
    {
        $incidents = $this->getNumIncidents([
            'operator' => $operatorId,
            'resolved' => 'true',
            'closed_date_start' => now()->startOf($timeString)->format('Y-m-d'),
        ]);

        $changes = count($this->resolvedChangeActivitiesByOperatorIdByTime($operatorId, $timeString)['results']);

        return $incidents + $changes;
    }

    /**
     * @param string $operatorId
     *
     * @return mixed
     */
    public function countOpenTicketsByOperator(string $operatorId): int
    {
        $incidents = $this->getNumIncidents([
            'operator' => $operatorId,
            'resolved' => 'false',
        ]);

        return $incidents + $this->countWaitingChangeActivitiesByOperatorId($operatorId);
    }

    /**
     * @param string $operatorId
     *
     * @return int
     */
    public function countActiveTicketsbyOperator(string $operatorId): int
    {
        $incidents = $this->getNumIncidents([
            'operator' => $operatorId,
            'resolved' => 'false',
            'processing_status' => [
                $this->getProcessingStatusId('Logged'),
                $this->getProcessingStatusId('In progress'),
                $this->getProcessingStatusId('Updated by user'),
            ],
        ]);

        return $incidents + $this->countWaitingChangeActivitiesByOperatorId($operatorId);
    }

    /**
     * @param string $operatorId
     *
     * @return int
     */
    public function countWaitingChangeActivitiesByOperatorId(string $operatorId): int
    {
        return count($this->waitingChangeActivitiesByOperatorId($operatorId));
    }

    /**
     * @param string $statusName
     *
     * @return int
     */
    public function countTicketsByStatus(string $statusName): int
    {
        $statusId = $this->getProcessingStatusId($statusName);

        return $this->countByProcessingStatusId($statusId);
    }

    /**
     * @return int
     */
    public function countUnassignedTickets(): int
    {
        return $this->getNumIncidents([
            'operator' => $this->getOperatorGroupId('I.T. Services'),
        ]);
    }
}
