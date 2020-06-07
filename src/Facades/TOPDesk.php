<?php

namespace FredBradley\TOPDesk\Facades;

use Illuminate\Support\Facades\Facade;

class TOPDesk extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'topdesk';
    }
}
