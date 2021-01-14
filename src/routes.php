<?php

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/topdesk'], function () {
    Route::get('counts', function () {
        return response()->json([
            'unresolvedIncidents' => TOPDesk::countOpenTickets(),
            'openChangeActivities' => count(TOPDesk::allOpenChangeActivities()),
            'loggedToday' => TOPDesk::countTicketsLoggedToday(), // + TOPDesk::countChangesLoggedToday(),
            'unassigned' => TOPDesk::countUnassignedTickets() + count(TOPDesk::unassignedWaitingChangeActivities()),
            'inProgress' => TOPDesk::countTicketsByStatus('In progress'),
            'waitingForUser' => TOPDesk::countTicketsByStatus('Waiting for user'),
            'updatedByUser' => TOPDesk::countTicketsByStatus('Updated by user'),
            'waitingForSupplier' => TOPDesk::countTicketsByStatus('Waiting for supplier'),
            'scheduled' => TOPDesk::countTicketsByStatus('Scheduled'),
            'dueThisWeek' => TOPDesk::countTicketsDueThisWeek(),
            'breachedTickets' => TOPDesk::countBreachedTickets(),
            'usersClosedCounts' => collect(TOPDesk::resolveCountsForOperatorGroup('I.T. Services',
                ['HELPDESK']))->sortByDesc('closed_week'),
        ]);
    });
});
