<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Traits;

use FredBradley\TOPDesk\Exceptions\PersonNotFound;

trait Persons
{
    /**
     * @throws PersonNotFound
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonByUsername(string $username): object
    {
        $result = self::query()->get('api/persons', [
            'query' => '(networkLoginName=='.$username.')',
        ])->throw()->collect();

        if ($result->isEmpty()) {
            // Pattern: named domain exception instead of the base \Exception class.
            // Callers can catch PersonNotFound specifically, and the 404 code means
            // HTTP layers (e.g. Handler::render) can map it to a response automatically.
            throw new PersonNotFound($username);
        }

        return (object) $result->first();
    }

    /**
     * Uses the v2 persons endpoint (/persons/{id}) which does not require the
     * intermediate /id/ segment present in older API versions.
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPersonById(string $id): object
    {
        return $this->get('api/persons/'.$id);
    }
}
