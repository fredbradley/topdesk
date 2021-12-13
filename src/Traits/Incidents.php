<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Facades\Log;

/**
 * Trait Incidents.
 */
trait Incidents
{
    /**
     * @param  string  $username
     * @return array
     */
    public function getOperatorByUsername(string $username): array
    {
        return Cacher::setAndGet('operator_'.$username, EasySeconds::months(1), function () use ($username) {
            Log::debug('Searching for Operator: '.$username);
            $result = $this->request('GET', 'api/operators', [], [
                'page_size' => 1,
                'query' => '(networkLoginName=='.$username.')',
            ]);

            if (is_null($result)) {
                throw new \Exception('Could not find an operator with the username: '.$username, 422);
            }

            if (count($result) == 1) {
                return $result[0];
            }

            return $result;
        });
    }

    /**
     * @param  string  $name
     * @return mixed
     */
    public function getOperatorGroupId(string $name): string
    {
        return Cacher::setAndGet('get_operator_group_name_'.$name, EasySeconds::months(1), function () use ($name) {
            $result = $this->request('GET', 'api/operatorgroups/lookup', [], ['name' => $name]);

            return $result['results'][0]['id'];
        });
    }

    /**
     * @param  string  $name
     * @return string
     */
    public function getProcessingStatusId(string $name): string
    {
        return Cacher::setAndGet('getProcessingStatusId_'.$name, EasySeconds::weeks(1), function () use ($name) {
            return $this->getProcessingStatus($name)['id'];
        });
    }

    /**
     * @param  string  $name
     * @return array
     */
    public function getProcessingStatus(string $name): array
    {
        return Cacher::setAndGet('status_'.$name, EasySeconds::weeks(1), function () use ($name) {
            $result = $this->request('GET', 'api/incidents/statuses');
            foreach ($result as $key => $val) {
                if ($val['name'] === $name) {
                    return $result[$key];
                }
            }

            return [];
        });
    }
}
