<?php

use Illuminate\Support\Facades\Route;
use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Cache;

Route::group(["prefix" => 'api/topdesk'], function () {
    Route::get('counts', function () {
        return Cache::remember('topdesk-counts', now()->addMinutes(2), function () {
            return response()->json([
                'open' => TOPDesk::countOpenTickets(),
                'logged' => TOPDesk::countLoggedTickets(),
                'unassigned' => TOPDesk::countUnassignedTickets(),
                'inProgress' => TOPDesk::countInProgressTickets(),
                'waitingForUser' => TOPDesk::countWaitingForUserTickets(),
                'updatedByUser' => TOPDesk::countUpdatedByUserTickets(),
                'waitingForSupplier' => TOPDesk::countWaitingForSupplier(),
                'scheduled' => TOPDesk::countScheduledTickets(),
            ]);
        });
    });
});
