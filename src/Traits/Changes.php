<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;

trait Changes
{
    public function allOpenChangeActivities(): array
    {
        return Cacher::remember('operatorChangeActivites', EasySeconds::minutes(10), function () {
            return $this->get('api/operatorChangeActivities', [
                'open' => 'true',
                'sort' => 'plannedFinalDate',
                'blocked' => 'false',
                'archived' => 'false',
            ])->results;
        });
    }

    public function unassignedWaitingChangeActivities(string $operatorGroupName = 'I.T. Services'): array
    {
        $operatorId = $this->getOperatorGroupId($operatorGroupName);

        return Cacher::remember(
            'unassignedWaitingChangeActivities_'.$operatorId,
            EasySeconds::minutes(10),
            function () use ($operatorId) {
                return $this->get('api/operatorChangeActivities', [
                    'open' => 'true',
                    'sort' => 'plannedFinalDate',
                    'blocked' => 'false',
                    'archived' => 'false',
                    'operator' => $operatorId,
                ])->results;
            }
        );
    }

    public function waitingChangeActivitiesByUsername(string $username): array
    {
        $operatorId = $this->getOperatorByUsername($username)->id;

        return $this->waitingChangeActivitiesByOperatorId($operatorId);
    }

    public function resolvedChangeActivitiesByOperatorIdByTime(string $operatorId, string $timeString = 'Week'): array
    {
        return Cacher::remember(
            'resolvedChangeActivitesByOperatorAndTime_'.$operatorId.'_'.$timeString,
            EasySeconds::hours(1),
            function () use ($operatorId, $timeString) {
                return $this->get('api/operatorChangeActivities', [
                    'open' => 'false',
                    'operator' => $operatorId,
                    'pageSize' => 1000,
                    'finalDateAfter' => now()->startOf($timeString)->format('Y-m-d'),
                ])->results;
            }
        );
    }

    public function waitingChangeActivitiesByOperatorId(string $operatorId): array
    {
        return Cacher::remember(
            'waitingChangeActivitiesByOperatorId_'.$operatorId,
            EasySeconds::hours(1),
            function () use ($operatorId) {
                return $this->get('api/operatorChangeActivities', [
                    'open' => 'true',
                    'sort' => 'plannedFinalDate',
                    'blocked' => 'false',
                    'archived' => 'false',
                    'operator' => $operatorId,
                ])->results;
            }
        );
    }
}
