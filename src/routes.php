<?php

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/topdesk'], function () {
    Route::get('counts', function () {
        return Cache::remember('topdesk-counts', now()->addMinutes(2), function () {
            return response()->json([
                'open' => TOPDesk::countOpenTickets(),
                'unassigned' => TOPDesk::countUnassignedTickets(),
                'logged' => TOPDesk::countTicketsByStatus('Logged'),
                'inProgress' => TOPDesk::countTicketsByStatus('In progress'),
                'waitingForUser' => TOPDesk::countTicketsByStatus('Waiting for user'),
                'updatedByUser' => TOPDesk::countTicketsByStatus('Updated by user'),
                'waitingForSupplier' => TOPDesk::countTicketsByStatus('Waiting for supplier'),
                'scheduled' => TOPDesk::countTicketsByStatus('Scheduled'),
                'usersClosedCounts' => TOPDesk::resolveCountsForOperatorGroup('I.T. Services',
                    ['TNSCSUPPORT', 'CMJO', 'HELPDESK']),
                'usersOpenCounts' => TOPDesk::openCountsForOperatorGroup('I.T. Services',
                    ['TNSCSUPPORT', 'CMJO', 'HELPDESK']),
            ]);
        });
    });
});
