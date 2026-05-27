<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Http;

// Shared fake helpers used in multiple tests:
// - operatorgroups/lookup  → resolves a group name to an ID
// - incidents/statuses     → resolves processing status names to IDs
// - incidents              → list of incidents for counting
// - operatorChangeActivities → change activity counts

it('counts tickets logged today for a given operator group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid', 'name' => 'I.T. Services']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2'], ['id' => 'inc-3']]),
    ]);

    $count = TOPDesk::countTicketsLoggedtoday('I.T. Services');

    expect($count)->toBe(3);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'grp-uuid')
        && str_contains($r->url(), 'creationDate')
    );
});

it('counts open tickets for a given operator group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'facilities-uuid']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2']]),
    ]);

    $count = TOPDesk::countOpenTickets('Facilities');

    expect($count)->toBe(2);
});

it('counts tickets due this week for a given operator group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1']]),
    ]);

    $count = TOPDesk::countTicketsDueThisWeek('I.T. Services');

    expect($count)->toBe(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'targetDate')
    );
});

it('counts breached tickets for a given operator group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2'], ['id' => 'inc-3'], ['id' => 'inc-4']]),
    ]);

    $count = TOPDesk::countBreachedTickets('I.T. Services');

    expect($count)->toBe(4);
});

it('counts tickets by a specific processing status id', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1']]),
    ]);

    $count = TOPDesk::countByProcessingStatusId('status-uuid', 'I.T. Services');

    expect($count)->toBe(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'processingStatus.id%3D%3Dstatus-uuid')
            || str_contains($r->url(), 'processingStatus.id==status-uuid')
    );
});

it('counts closed tickets for an operator within a time period', function () {
    Http::fake(['*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2']])]);

    $count = TOPDesk::countClosedTicketsByTime('op-uuid', 'week');

    expect($count)->toBe(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'operator.id%3D%3Dop-uuid')
            || str_contains($r->url(), 'operator.id==op-uuid')
    );
});

it('countResolvesByTime delegates to countClosedTicketsByTime', function () {
    Http::fake(['*api/incidents*' => Http::response([['id' => 'inc-1']])]);

    $count = TOPDesk::countResolvesByTime('op-uuid', 'week');

    expect($count)->toBe(1);
});

it('counts open tickets for an operator including waiting change activities', function () {
    Http::fake([
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2']]),
        '*api/operatorChangeActivities*' => Http::response(['results' => [['id' => 'ca-1']]]),
    ]);

    // 2 open incidents + 1 waiting change activity = 3
    $count = TOPDesk::countOpenTicketsByOperator('op-uuid');

    expect($count)->toBe(3);
});

it('counts active tickets for an operator using processing status ids', function () {
    Http::fake([
        '*api/incidents/statuses*' => Http::response([
            ['id' => 'logged-id', 'name' => 'Logged'],
            ['id' => 'inprogress-id', 'name' => 'In progress'],
            ['id' => 'updated-id', 'name' => 'Updated by user'],
        ]),
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2']]),
    ]);

    $count = TOPDesk::countActiveTicketsbyOperator('op-uuid');

    expect($count)->toBe(2);
});

it('counts waiting change activities for an operator', function () {
    Http::fake(['*api/operatorChangeActivities*' => Http::response(['results' => [
        ['id' => 'ca-1'],
        ['id' => 'ca-2'],
    ]])]);

    $count = TOPDesk::countWaitingChangeActivitiesByOperatorId('op-uuid');

    expect($count)->toBe(2);
});

it('counts tickets by processing status name', function () {
    Http::fake([
        '*api/incidents/statuses*' => Http::response([
            ['id' => 'waiting-id', 'name' => 'Waiting for user'],
        ]),
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2'], ['id' => 'inc-3']]),
    ]);

    $count = TOPDesk::countTicketsByStatus('Waiting for user', 'I.T. Services');

    expect($count)->toBe(3);
});

it('counts unassigned tickets for an operator group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/incidents*' => Http::response([['id' => 'inc-1']]),
    ]);

    $count = TOPDesk::countUnassignedTickets('I.T. Services');

    expect($count)->toBe(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents'));
});
