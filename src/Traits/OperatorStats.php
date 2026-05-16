<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\EasyTime\EasySeconds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait OperatorStats
{
    /*
     * Pattern: return Collection instead of a plain array from API calls.
     * Collection gives callers chainable higher-order operations (filter, map,
     * pluck, etc.) without forcing them to write their own foreach loops.
     */

    public function getOperatorsByOperatorGroup(string $name): Collection
    {
        $operatorGroupId = $this->getOperatorGroupId($name);

        return Cache::remember(
            'get_operators_'.$operatorGroupId,
            EasySeconds::weeks(1),
            fn () => collect($this->get('api/operators', [
                'page_size' => 100,
                'query' => '(operatorGroup.id=='.$operatorGroupId.')',
            ]))
        );
    }

    public function openCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        return $this->getOperatorsByOperatorGroup($name)
            ->reject(fn ($operator) => in_array($operator->networkLoginName, $ignoreUsernames))
            ->mapWithKeys(fn ($operator) => [$operator->networkLoginName => $this->countOpenTicketsByOperator($operator->id)])
            ->all();
    }

    public function activeCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        /*
         * Pattern: consistent property access on stdClass objects returned by
         * the HTTP layer. Previously this method used array access ($operator['id'])
         * while the surrounding methods used property access ($operator->id) on the
         * same objects — now unified to property access throughout.
         */
        return $this->getOperatorsByOperatorGroup($name)
            ->reject(fn ($operator) => in_array($operator->networkLoginName, $ignoreUsernames))
            ->mapWithKeys(fn ($operator) => [$operator->networkLoginName => $this->countActiveTicketsByOperator($operator->id)])
            ->all();
    }

    /**
     * @deprecated Use closedTicketCountsForOperatorGroup
     */
    public function resolveCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        return $this->closedTicketCountsForOperatorGroup($name, $ignoreUsernames);
    }

    public function closedTicketCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $lowerIgnore = array_map('strtolower', $ignoreUsernames);

        return $this->getOperatorsByOperatorGroup($name)
            ->reject(fn ($operator) => in_array(strtolower($operator->networkLoginName), $lowerIgnore))
            ->mapWithKeys(fn ($operator) => [$operator->networkLoginName => $this->getClosedIncidentsForOperator($operator->id)])
            ->all();
    }

    /**
     * @deprecated Use getClosedIncidentsForOperator
     */
    public function getResolvedIncidentsForOperator(string $operatorId): array
    {
        return $this->getClosedIncidentsForOperator($operatorId);
    }

    public function getClosedIncidentsForOperator(string $operatorId): array
    {
        return Cache::remember(
            'resolvedIncidentsByOperator_'.$operatorId,
            EasySeconds::minutes(5),
            function () use ($operatorId) {
                $results = collect($this->get('api/incidents', [
                    'operator' => $operatorId,
                    'pageSize' => 10000,
                ]));

                return [
                    'closed_day' => $results->where('closedDate', '>', now()->startOfDay())->count(),
                    'closed_week' => $results->where('closedDate', '>', now()->startOf('week'))->count(),
                    'closed_month' => $results->where('closedDate', '>', now()->startOfMonth())->count(),
                    'closed_total' => $results->where('closed', '=', true)->count(),
                    'open' => $results->where('closed', '!=', true)->count(),
                ];
            }
        );
    }

    /**
     * @deprecated Use getClosedIncidentsForOperator
     */
    public function getResolvedTicketsForOperator(string $operatorId): array
    {
        return $this->getClosedIncidentsForOperator($operatorId);
    }
}
