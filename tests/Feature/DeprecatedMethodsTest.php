<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Http;

// Each deprecated method delegates to countTicketsByStatus(), which in turn
// resolves the processing status ID and operator group ID before counting.
// All three HTTP endpoints must be faked per test.

function fakeStatusGroupAndIncidents(string $statusName, int $incidentCount = 2): void
{
    Http::fake([
        '*api/incidents/statuses*' => Http::response([
            ['id' => 'status-uuid', 'name' => $statusName],
        ]),
        '*api/operatorgroups/lookup*' => Http::response([
            'results' => [['id' => 'grp-uuid', 'name' => 'I.T. Services']],
        ]),
        '*api/incidents*' => Http::response(
            array_map(fn ($i) => ['id' => "inc-{$i}"], range(1, $incidentCount))
        ),
    ]);
}

it('countWaitingForUserTickets delegates to countTicketsByStatus', function () {
    fakeStatusGroupAndIncidents('Waiting for user', 3);

    $count = TOPDesk::countWaitingForUserTickets();

    expect($count)->toBe(3);
});

it('countUpdatedByUserTickets delegates to countTicketsByStatus', function () {
    fakeStatusGroupAndIncidents('Updated by user', 1);

    $count = TOPDesk::countUpdatedByUserTickets();

    expect($count)->toBe(1);
});

it('countWaitingForSupplier delegates to countTicketsByStatus', function () {
    fakeStatusGroupAndIncidents('Waiting for supplier', 5);

    $count = TOPDesk::countWaitingForSupplier();

    expect($count)->toBe(5);
});

it('countScheduledTickets delegates to countTicketsByStatus', function () {
    fakeStatusGroupAndIncidents('Scheduled', 2);

    $count = TOPDesk::countScheduledTickets();

    expect($count)->toBe(2);
});

it('countInProgressTickets delegates to countTicketsByStatus', function () {
    fakeStatusGroupAndIncidents('In progress', 4);

    $count = TOPDesk::countInProgressTickets();

    expect($count)->toBe(4);
});

it('countLoggedTickets delegates to countTicketsByStatus', function () {
    fakeStatusGroupAndIncidents('Logged', 7);

    $count = TOPDesk::countLoggedTickets();

    expect($count)->toBe(7);
});
