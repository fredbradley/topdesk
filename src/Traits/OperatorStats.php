<?php

namespace FredBradley\TOPDesk\Traits;

use FredBradley\Cacher\Cacher;
use FredBradley\Cacher\EasyMinutes;

/**
 * Trait OperatorStats.
 */
trait OperatorStats
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOperatorsByOperatorGroup(string $name)
    {
        $operatorGroupId = $this->getOperatorGroupId($name);

        return Cacher::setAndGet('get_operators_' . $operatorGroupId, EasyMinutes::A_MONTH,
            function () use ($operatorGroupId) {
                return $this->request(
                    'GET',
                    'api/operators',
                    [],
                    [
                        'page_size' => 100,
                        'query' => '(operatorGroup.id==' . $operatorGroupId . ')',
                    ]
                );
            });
    }


    /**
     * @param string $name
     * @param array  $ignoreUsernames
     *
     * @return array
     */
    public function openCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $operators = $this->getOperatorsByOperatorGroup($name);
        $results = [];
        foreach ($operators as $operator) {
            if (! in_array($operator[ 'networkLoginName' ], $ignoreUsernames)) {
                $results[ $operator[ 'networkLoginName' ] ] = $this->countOpenTicketsByOperator($operator[ 'id' ]);
            }
        }

        return $results;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function resolveCountsForOperatorGroup(string $name = 'I.T. Services', array $ignoreUsernames = []): array
    {
        $operators = $this->getOperatorsByOperatorGroup($name);
        $results = [];
        foreach ($operators as $operator) {
            if (! in_array($operator[ 'networkLoginName' ], $ignoreUsernames)) {
                foreach (['day', 'week', 'month', 'year'] as $timeSpan) {
                    $results[ $operator[ 'networkLoginName' ] ][ $timeSpan ] = $this->countResolvesByTime($operator[ 'id' ],
                        $timeSpan);
                }
            }
        }

        return $results;
    }
}
