<?php

namespace FredBradley\TOPDesk\Traits;

trait Changes
{
    public function allOpenChangeActivities()
    {
        return $this->request('GET', 'api/operatorChangeActivities', [], [
            'open' => 'true',
            'sort' => 'plannedFinalDate',
            'blocked' => 'false',
            'archived' => 'false',
        ])['results'];
    }

    public function unassignedWaitingChangeActivities()
    {
        $operatorId = $this->getOperatorGroupId('I.T. Services');

        return $this->request('GET', 'api/operatorChangeActivities', [], [
            'open' => 'true',
            'sort' => 'plannedFinalDate',
            'blocked' => 'false',
            'archived' => 'false',
            'operatorGroup' => $operatorId,
        ])['results'];
    }

    public function waitingChangeActivitiesByUsername(string $username)
    {
        $operatorId = $this->getOperatorByUsername($username)['id'];

        return $this->waitingChangeActivitiesByOperatorId($operatorId);
    }

    public function resolvedChangeActivitiesByOperatorIdByTime(string $operatorId, string $timeString)
    {
        return $this->request('GET', 'api/operatorChangeActivities', [], [
            'open' => 'false',
            'operator' => $operatorId,
            'pageSize' => 1000,
            'finalDateAfter' => now()->startOf($timeString)->format('Y-m-d'),
        ]);
    }

    public function waitingChangeActivitiesByOperatorId(string $operatorId)
    {
        return $this->request('GET', 'api/operatorChangeActivities', [], [
            'open' => 'true',
            'sort' => 'plannedFinalDate',
            'blocked' => 'false',
            'archived' => 'false',
            'operator' => $operatorId,
        ])['results'];
    }
}
