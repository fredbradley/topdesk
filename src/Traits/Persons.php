<?php

namespace FredBradley\TOPDesk\Traits;

use Illuminate\Http\Client\RequestException;

trait Persons
{
    /**
     * @param  string  $username
     * @return object
     *
     * @throws RequestException
     */
    public function getPersonByUsername(string $username): object
    {
        $result = self::query()->get('api/persons', [
            'query' => '(networkLoginName=='.$username.')',
        ])->throw()->collect();

        if ($result->isEmpty()) {
            throw new \Exception('Person Not Found', 404);
        }

        return (object) $result->first();
    }

    /**
     * @throws RequestException
     */
    public function getPersonById(string $id): object
    {
        return $this->get('api/persons/id/'.$id);
    }
}
