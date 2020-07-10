<?php

use FredBradley\TOPDesk\Facades\TOPDesk;
use FredBradley\Cacher\Cacher;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/topdesk'], function () {
    Route::get('counts', function () {
        return Cacher::setAndGet('topdesk-counts', 2, function () {
            return response()->json([
                'open' => TOPDesk::countOpenTickets() + count(TOPDesk::allOpenChangeActivities()),
                'unassigned' => TOPDesk::countUnassignedTickets() + count(TOPDesk::unassignedWaitingChangeActivities()),
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
