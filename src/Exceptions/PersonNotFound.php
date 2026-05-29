<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Exceptions;

/**
 * Thrown when a person lookup returns no results.
 * Carries HTTP 404 so callers can map it to a response code directly.
 */
class PersonNotFound extends \RuntimeException
{
    public function __construct(string $identifier)
    {
        parent::__construct("Person not found: {$identifier}", 404);
    }
}
