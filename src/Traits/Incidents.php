<?php

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Trait Incidents
 * @package FredBradley\TOPDesk\Traits
 */
trait Incidents
{
    /**
     * @return int
     */
    public function countOpenTickets(): int
    {
        $result = $this->getNumIncidents([
            'resolved' => 'false',
        ]);
        return $result;
    }

    /**
     * @return int
     */
    public function countLoggedTickets(): int
    {
        $statusId = $this->getProcessingStatusId('Logged');
        return $this->countByProcessingStatusId($statusId);
    }

    /**
     * @param string $processingStatusId
     *
     * @return int
     */
    public function countByProcessingStatusId(string $processingStatusId): int
    {
        return $this->getNumIncidents(['processing_status' => $processingStatusId]);
    }

    /**
     * @param array $options
     *
     * @return int
     */
    public function getNumIncidents(array $options): int
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
        return count(json_decode((string)$response->getBody(), true));
    }

    public function countUnassignedTickets(): int
    {
        return $this->getNumIncidents([
            'operator' => $this->getOperatorId('I.T. Services', 'operatorITServices'),
        ]);
    }

    public function getOperatorId(string $name)
    {
        return Cache::rememberForever('get_operator_name_' . $name, function () use ($name) {
            $result = $this->request('GET', 'api/operatorgroups/lookup', [], ['name' => $name]);
            return $result[ 'results' ][ 0 ][ 'id' ];
        });
    }

    /**
     * @return int
     */
    public function countInProgressTickets(): int
    {
        $inProgressId = $this->getProcessingStatusId('In progress');
        return $this->countByProcessingStatusId($inProgressId);
    }

    /**
     * @return int
     */
    public function countWaitingForUserTickets(): int
    {
        $statusId = $this->getProcessingStatusId('Waiting for user');
        return $this->countByProcessingStatusId($statusId);
    }

    /**
     * @return int
     */
    public function countUpdatedByUserTickets(): int
    {
        $statusId = $this->getProcessingStatusId('Updated by user');
        return $this->countByProcessingStatusId($statusId);
    }

    /**
     * @return int
     */
    public function countWaitingForSupplier(): int
    {
        $statusId = $this->getProcessingStatusId('Waiting for supplier');
        return $this->countByProcessingStatusId($statusId);
    }

    /**
     * @return int
     */
    public function countScheduledTickets(): int
    {
        $statusId = $this->getProcessingStatusId('Scheduled');
        return $this->countByProcessingStatusId($statusId);
    }


    /**
     * @param string $name
     *
     * @return string
     */
    public function getProcessingStatusId(string $name): string
    {
        return $this->getProcessingStatus($name)[ 'id' ];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getProcessingStatus(string $name): array
    {
        return Cache::rememberForever('status_' . $name, function () use ($name) {
            $result = $this->request("GET", "api/incidents/statuses");
            foreach ($result as $key => $val) {
                if ($val[ 'name' ] === $name) {
                    return $result[ $key ];
                }
            }
            return [];
        });

    }
}
