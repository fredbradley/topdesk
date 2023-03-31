<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\EasyTime\EasySeconds;

/**
 * Trait OperatorStats.
 */
trait OperatorStats
{
    /**
     * @param  string  $name
     * @return array
     */
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

    /**
     * @param  string  $name
     * @param  array  $ignoreUsernames
     * @return array
     */
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

    /**
     * @param  string  $name
     * @param  array  $ignoreUsernames
     * @return array
     */
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
     * @param  string  $name
     * @param  array  $ignoreUsernames
     * @return array
     */
    public function resolveCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $operators = $this->getOperatorsByOperatorGroup($name);
        $results = [];

        foreach ($operators as $operator) {
            if (! in_array(strtolower($operator->networkLoginName), array_map('strtolower',$ignoreUsernames))) {

                $results[$operator->networkLoginName] = $this->getResolvedTicketsForOperator($operator->id);
            }
        }

        return $results;
    }

    /**
     * @param  string  $operatorId
     * @return array
     */
    public function getResolvedIncidentsForOperator(string $operatorId): array
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
     * Gets all closed change activities, and separates them into day, week, month, total array.
     * Have to have 'open' as a key, because of the calculation at self::getResolvedTicketsForOperator
     * In the collection we are then also only showing change activities that are not skipped!
     *
     * @param  string  $operatorId
     * @return array
     */
    public function getResolvedChangeActivitiesForOperator(string $operatorId): array
    {
        Cacher::forget('resolvedChangeActivitesByOperatorAndTime_'.$operatorId);

        return Cacher::remember(
            'resolvedChangeActivitesByOperatorAndTime_'.$operatorId,
            EasySeconds::minutes(5),
            function () use ($operatorId) {
                $results = collect($this->get('api/operatorChangeActivities', [
                    'open' => 'false',
                    'operator' => $operatorId,
                    'pageSize' => 5000,
                ])->results);

                return [
                    'closed_day' => $results->where('processingStatus', '!=', 'skipped')->where('finalDate', '>', now()->startOfDay())->count(),
                    'closed_week' => $results->where('processingStatus', '!=', 'skipped')->where('finalDate', '>', now()->startOf('week'))->count(),
                    'closed_month' => $results->where('processingStatus', '!=', 'skipped')->where('finalDate', '>', now()->startOfMonth())->count(),
                    'closed_total' => $results->where('processingStatus', '!=', 'skipped')->where('finalDate', '=', true)->count(),
                    'open' => null,
                ];
            }
        );
    }

    /**
     * Is the sum of Incidents and Change Activities...
     *
     * @param  string  $operatorId
     * @return array
     */
    public function getResolvedTicketsForOperator(string $operatorId): array
    {
        return $this->sumTwoArrays(
            $this->getResolvedIncidentsForOperator($operatorId),
            $this->getResolvedChangeActivitiesForOperator($operatorId)
        );
    }

    /**
     * @param  array  $arrayOne
     * @param  array  $arrayTwo
     * @return array
     */
    private function sumTwoArrays(array $arrayOne, array $arrayTwo): array
    {
        $sums = [];

        foreach (array_keys($arrayOne + $arrayTwo) as $total) {
            $sums[$total] = (isset($arrayOne[$total]) ? $arrayOne[$total] : 0) + (isset($arrayTwo) ? $arrayTwo[$total] : 0);
        }

        return $sums;
    }
}
