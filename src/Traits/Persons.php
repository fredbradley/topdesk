<?php

namespace FredBradley\TOPDesk\Traits;

trait Persons
{
    /**
     * @param  string  $username
     * @return array
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonsByUsername(string $username): ?object
    {
        return collect($this->get('api/persons', [
            'query' => '(networkLoginName=='.$username.')',
        ]))->first();
    }

    /**
     * @deprecated Use getPersonsByUsername instead.
     *
     * Will be refactored to retrieve a different value in a future release.
     *
     * @param  string  $username
     * @return object
     */
    public function getPersonByUsername(string $username): object
    {
        return collect($this->getPersonsByUsername($username))->first();
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonById(string $id): object
    {
        return $this->get('api/persons/id/'.$id);
    }
}
