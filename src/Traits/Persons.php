<?php

namespace FredBradley\TOPDesk\Traits;

trait Persons
{
    /**
     * @param  string  $username
     * @return mixed
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonsByUsername(string $username): array
    {
        return $this->get('api/persons', [
            'query' => '(networkLoginName=='.$username.')',
        ]);
    }

    /**
     * @deprecated Use getPersonsByUsername instead.
     *
     * Will be refactored to retrieve a different value in a future release.
     *
     * @param  string  $username
     * @return mixed
     */
    public function getPersonByUsername(string $username): array
    {
        return $this->getPersonsByUsername($username);
    }
}
