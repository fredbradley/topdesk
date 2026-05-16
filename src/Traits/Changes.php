<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait Changes
{
    /*
     * Pattern: return Collection instead of array.
     * The TOPdesk API wraps paginated results in a {results: [...]} envelope.
     * Wrapping in collect() gives callers first(), filter(), map(), etc. for free
     * and makes the return type explicit rather than leaking the API's envelope shape.
     */

    public function allOpenChangeActivities(): Collection
    {
        return Cache::remember('operatorChangeActivites', EasySeconds::minutes(10), function () {
            return collect($this->get('api/operatorChangeActivities', [
                'open' => 'true',
                'sort' => 'plannedFinalDate',
                'blocked' => 'false',
                'archived' => 'false',
            ])->results ?? []);
        });
    }

    public function unassignedWaitingChangeActivities(string $operatorGroupName = 'I.T. Services'): Collection
    {
        $operatorId = $this->getOperatorGroupId($operatorGroupName);

        return Cache::remember(
            'unassignedWaitingChangeActivities_'.$operatorId,
            EasySeconds::minutes(10),
            fn () => collect($this->get('api/operatorChangeActivities', [
                'open' => 'true',
                'sort' => 'plannedFinalDate',
                'blocked' => 'false',
                'archived' => 'false',
                'operator' => $operatorId,
            ])->results ?? [])
        );
    }

    public function waitingChangeActivitiesByUsername(string $username): Collection
    {
        return $this->waitingChangeActivitiesByOperatorId($this->getOperatorByUsername($username)->id);
    }

    public function resolvedChangeActivitiesByOperatorIdByTime(string $operatorId, string $timeString = 'Week'): Collection
    {
        return Cache::remember(
            'resolvedChangeActivitesByOperatorAndTime_'.$operatorId.'_'.$timeString,
            EasySeconds::hours(1),
            fn () => collect($this->get('api/operatorChangeActivities', [
                'open' => 'false',
                'operator' => $operatorId,
                'pageSize' => 1000,
                'finalDateAfter' => now()->startOf($timeString)->format('Y-m-d'),
            ])->results ?? [])
        );
    }

    public function waitingChangeActivitiesByOperatorId(string $operatorId): Collection
    {
        return Cache::remember(
            'waitingChangeActivitiesByOperatorId_'.$operatorId,
            EasySeconds::hours(1),
            fn () => collect($this->get('api/operatorChangeActivities', [
                'open' => 'true',
                'sort' => 'plannedFinalDate',
                'blocked' => 'false',
                'archived' => 'false',
                'operator' => $operatorId,
            ])->results ?? [])
        );
    }
}
