<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;

/**
 * Trait OperatorStats.
 */
trait OperatorStats
{
    public function getOperatorsByOperatorGroup(string $name): array
    {
        $operatorGroupId = $this->getOperatorGroupId($name);

        return Cacher::remember(
            'get_operators_'.$operatorGroupId,
            EasySeconds::weeks(1),
            function () use ($operatorGroupId) {
                return $this->get(
                    'api/operators',
                    [
                        'page_size' => 100,
                        'query' => '(operatorGroup.id=='.$operatorGroupId.')',
                    ]
                );
            }
        );
    }

    public function openCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $operators = $this->getOperatorsByOperatorGroup($name);

        $results = [];
        foreach ($operators as $operator) {
            if (! in_array($operator->networkLoginName, $ignoreUsernames)) {
                $results[$operator->networkLoginName] = $this->countOpenTicketsByOperator($operator->id);
            }
        }

        return $results;
    }

    public function activeCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $operators = $this->getOperatorsByOperatorGroup($name);
        $results = [];
        foreach ($operators as $operator) {
            if (! in_array($operator['networkLoginName'], $ignoreUsernames)) {
                $results[$operator['networkLoginName']] = $this->countActiveTicketsByOperator($operator['id']);
            }
        }

        return $results;
    }

    /**
     * @deprecated Use closedTicketCountsForOperatorGroup
     */
    public function resolveCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsername = []): array
    {
        return $this->closedTicketCountsForOperatorGroup($name, $ignoreUsername);
    }

    public function closedTicketCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $operators = $this->getOperatorsByOperatorGroup($name);
        $results = [];

        foreach ($operators as $operator) {
            if (! in_array(strtolower($operator->networkLoginName), array_map('strtolower', $ignoreUsernames))) {
                $results[$operator->networkLoginName] = $this->getResolvedIncidentsForOperator($operator->id); // changed method to getResolvedIncidentsForOperator to as not to include change requests, which we know longer have access to
            }
        }

        return $results;
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
        return Cacher::remember(
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
     * Is the sum of Incidents and Change Activities...
     *
     *
     * @deprecated
     */
    public function getResolvedTicketsForOperator(string $operatorId): array
    {
        return $this->getClosedIncidentsForOperator($operatorId);
    }

    private function sumTwoArrays(array $arrayOne, array $arrayTwo): array
    {
        $sums = [];

        foreach (array_keys($arrayOne + $arrayTwo) as $total) {
            $sums[$total] = (isset($arrayOne[$total]) ? $arrayOne[$total] : 0) + (isset($arrayTwo) ? $arrayTwo[$total] : 0);
        }

        return $sums;
    }
}
