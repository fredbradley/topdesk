<?php

namespace FredBradley\TOPDesk\Traits;

trait Persons
{
    /**
     * @param  string  $username
     * @return mixed
     */
    public function getPersonByUsername(string $username): object
    {
        $result = self::query()->get( 'api/persons', [
            'query' => '(networkLoginName=='.$username.')',
        ])->throw()->collect();

        if ($result->isEmpty()) {
            throw new \Exception("Person Not Found");
        }

        return (object) $result->first();
    }
}
