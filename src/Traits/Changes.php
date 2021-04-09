<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;

trait Changes
{
    public function allOpenChangeActivities()
    {
        return Cacher::setAndGet('operatorChangeActivites', EasySeconds::minutes(9), function () {
            return $this->request('GET', 'api/operatorChangeActivities', [], [
                'open' => 'true',
                'sort' => 'plannedFinalDate',
                'blocked' => 'false',
                'archived' => 'false',
            ])['results'];
        });
    }

    public function unassignedWaitingChangeActivities()
    {
        $operatorId = $this->getOperatorGroupId('I.T. Services');

        return Cacher::setAndGet(
            'unassignedWaitingChangeActivities_'.$operatorId,
            EasySeconds::minutes(8),
            function () use ($operatorId) {
                return $this->request('GET', 'api/operatorChangeActivities', [], [
                    'open' => 'true',
                    'sort' => 'plannedFinalDate',
                    'blocked' => 'false',
                    'archived' => 'false',
                    'operatorGroup' => $operatorId,
                ])['results'];
            }
        );
    }

    public function waitingChangeActivitiesByUsername(string $username)
    {
        $operatorId = Cacher::setAndGet(
            'getOperatorByUsername_'.$username,
            EasySeconds::hours(1),
            function () use ($username) {
                return $this->getOperatorByUsername($username)['id'];
            }
        );

        return $this->waitingChangeActivitiesByOperatorId($operatorId);
    }

    public function resolvedChangeActivitiesByOperatorIdByTime(string $operatorId, string $timeString)
    {
        return Cacher::setAndGet(
            'resolvedChangeActivitesByOperatorAndTime_'.$operatorId.'_'.$timeString,
            EasySeconds::hours(1),
            function () use ($operatorId, $timeString) {
                return $this->request('GET', 'api/operatorChangeActivities', [], [
                    'open' => 'false',
                    'operator' => $operatorId,
                    'pageSize' => 1000,
                    'finalDateAfter' => now()->startOf($timeString)->format('Y-m-d'),
                ]);
            }
        );
    }

    public function waitingChangeActivitiesByOperatorId(string $operatorId)
    {
        return Cacher::setAndGet(
            'waitingChangeActivitiesByOperatorId_'.$operatorId,
            EasySeconds::hours(1),
            function () use ($operatorId) {
                return $this->request('GET', 'api/operatorChangeActivities', [], [
                    'open' => 'true',
                    'sort' => 'plannedFinalDate',
                    'blocked' => 'false',
                    'archived' => 'false',
                    'operator' => $operatorId,
                ])['results'];
            }
        );
    }
}
