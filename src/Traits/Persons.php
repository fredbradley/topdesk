<?php

namespace FredBradley\TOPDesk\Traits;

trait Persons
{
    /**
     * @param string $username
     *
     * @return mixed
     */
    public function getPersonByUsername(string $username)
    {
        return $this->request('GET', 'api/persons', [], [
            'query' => '(networkLoginName=='.$username.')',
        ]);
    }
}
