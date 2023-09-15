<?php

namespace FredBradley\TOPDesk\Traits;

trait Persons
{
    /**
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonByUsername(string $username): object
    {
        $result = self::query()->get('api/persons', [
            'query' => '(networkLoginName=='.$username.')',
        ])->throw()->collect();

        if ($result->isEmpty()) {
            throw new \Exception('Person Not Found');
        }

        return (object) $result->first();
    }

    /**
     * @deprecated Use getPersonsByUsername instead.
     *
     * Will be refactored to retrieve a different value in a future release.
     */
    public function getPersonByUsername(string $username): object
    {
        return $this->getPersonsByUsername($username);
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonById(string $id): object
    {
        return $this->get('api/persons/id/'.$id);
    }
}
