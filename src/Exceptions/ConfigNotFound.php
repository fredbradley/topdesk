<?php

namespace FredBradley\TOPDesk\Exceptions;

use Throwable;

class ConfigNotFound extends \Exception
{
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
