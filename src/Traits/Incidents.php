<?php

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
        return count(json_decode((string)$response->getBody(), true));
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


    /**
     * @param string $username
     *
     * @return array
     */
    public function getOperatorByUsername(string $username): array
    {
        return Cache::rememberForever('operator_' . $username, function () use ($username) {
            $result = $this->request('GET', 'api/operators', [], [
                'page_size' => 1,
                'query' => '(networkLoginName==' . $username . ')',
            ]);
            if (count($result) == 1) {
                return $result[ 0 ];
            }
            return $result;
        });
    }


    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOperatorGroupId(string $name): string
    {
        return Cache::rememberForever('get_operator_group_name_' . $name, function () use ($name) {
            $result = $this->request('GET', 'api/operatorgroups/lookup', [], ['name' => $name]);
            return $result[ 'results' ][ 0 ][ 'id' ];
        });
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
