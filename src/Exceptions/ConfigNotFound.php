<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Exceptions;

// Pattern: exception classes only need a body when they add behaviour beyond their parent.
// An empty class body inherits Exception's full constructor/message handling for free.
class ConfigNotFound extends \RuntimeException
{
}
