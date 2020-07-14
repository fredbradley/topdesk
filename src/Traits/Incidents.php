<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\Cacher\EasyMinutes;

/**
 * Trait Incidents.
 */
trait Incidents
{


    /**
     * @param string $username
     *
     * @return array
     */
    public function getOperatorByUsername(string $username): array
    {
        return Cacher::setAndGet('operator_' . $username, EasyMinutes::A_YEAR, function () use ($username) {
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
        return Cacher::setAndGet('get_operator_group_name_' . $name, EasyMinutes::A_YEAR, function () use ($name) {
            $result = $this->request('GET', 'api/operatorgroups/lookup', [], ['name' => $name]);

            return $result[ 'results' ][ 0 ][ 'id' ];
        });
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
        return Cacher::setAndGet('status_' . $name, EasyMinutes::A_YEAR, function () use ($name) {
            $result = $this->request('GET', 'api/incidents/statuses');
            foreach ($result as $key => $val) {
                if ($val[ 'name' ] === $name) {
                    return $result[ $key ];
                }
            }

            return [];
        });
    }
}
