<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('returns operators in a group as a Collection', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid', 'name' => 'I.T. Services']]]),
        '*api/operators*' => Http::response([
            ['id' => 'op-1', 'networkLoginName' => 'jsmith'],
            ['id' => 'op-2', 'networkLoginName' => 'bjones'],
        ]),
    ]);

    $result = TOPDesk::getOperatorsByOperatorGroup('I.T. Services');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operators')
        && str_contains($r->url(), 'grp-uuid')
    );
});

it('caches operator group members and hits API only once', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([['id' => 'op-1', 'networkLoginName' => 'jsmith']]),
    ]);

    TOPDesk::getOperatorsByOperatorGroup('I.T. Services');
    TOPDesk::getOperatorsByOperatorGroup('I.T. Services');

    Http::assertSentCount(2); // 1 for lookup + 1 for operators (both cached after first)
});

it('returns open ticket counts keyed by login name', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([['id' => 'op-1', 'networkLoginName' => 'jsmith']]),
        '*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2']]),
        '*api/operatorChangeActivities*' => Http::response(['results' => []]),
    ]);

    $result = TOPDesk::openCountsForOperatorGroup('I.T. Services');

    expect($result)->toBeArray()->toHaveKey('jsmith');
    expect($result['jsmith'])->toBe(2);
});

it('excludes specified usernames from open counts', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([
            ['id' => 'op-1', 'networkLoginName' => 'jsmith'],
            ['id' => 'op-2', 'networkLoginName' => 'bjones'],
        ]),
        '*api/incidents*' => Http::response([['id' => 'inc-1']]),
        '*api/operatorChangeActivities*' => Http::response(['results' => []]),
    ]);

    $result = TOPDesk::openCountsForOperatorGroup('I.T. Services', ['bjones']);

    expect($result)->toBeArray()->toHaveKey('jsmith')->not->toHaveKey('bjones');
});

it('returns active ticket counts keyed by login name', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([['id' => 'op-1', 'networkLoginName' => 'jsmith']]),
        '*api/incidents/statuses*' => Http::response([
            ['id' => 'logged-id', 'name' => 'Logged'],
            ['id' => 'inprogress-id', 'name' => 'In progress'],
            ['id' => 'updated-id', 'name' => 'Updated by user'],
        ]),
        '*api/incidents*' => Http::response([['id' => 'inc-1']]),
    ]);

    $result = TOPDesk::activeCountsForOperatorGroup('I.T. Services');

    expect($result)->toBeArray()->toHaveKey('jsmith');
    expect($result['jsmith'])->toBe(1);
});

it('resolveCountsForOperatorGroup delegates to closedTicketCountsForOperatorGroup', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([['id' => 'op-1', 'networkLoginName' => 'jsmith']]),
        '*api/incidents*' => Http::response([
            ['id' => 'inc-1', 'closed' => true, 'closedDate' => now()->toIso8601String()],
        ]),
    ]);

    $result = TOPDesk::resolveCountsForOperatorGroup('I.T. Services');

    expect($result)->toBeArray()->toHaveKey('jsmith');
});

it('returns closed ticket counts for all operators in a group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([
            ['id' => 'op-1', 'networkLoginName' => 'jsmith'],
            ['id' => 'op-2', 'networkLoginName' => 'BJONES'],
        ]),
        '*api/incidents*' => Http::response([
            ['id' => 'inc-1', 'closed' => true, 'closedDate' => now()->toIso8601String()],
            ['id' => 'inc-2', 'closed' => false, 'closedDate' => null],
        ]),
    ]);

    $result = TOPDesk::closedTicketCountsForOperatorGroup('I.T. Services');

    expect($result)->toBeArray()
        ->toHaveKey('jsmith')
        ->toHaveKey('BJONES');
});

it('excludes ignored usernames case-insensitively from closed counts', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid']]]),
        '*api/operators*' => Http::response([
            ['id' => 'op-1', 'networkLoginName' => 'jsmith'],
            ['id' => 'op-2', 'networkLoginName' => 'BJONES'],
        ]),
        '*api/incidents*' => Http::response([]),
    ]);

    // Ignore 'bjones' (lowercase) should exclude 'BJONES' (uppercase) too
    $result = TOPDesk::closedTicketCountsForOperatorGroup('I.T. Services', ['bjones']);

    expect($result)->toBeArray()->toHaveKey('jsmith')->not->toHaveKey('BJONES');
});

it('getResolvedIncidentsForOperator delegates to getClosedIncidentsForOperator', function () {
    Http::fake(['*api/incidents*' => Http::response([
        ['id' => 'inc-1', 'closed' => true, 'closedDate' => now()->toIso8601String()],
    ])]);

    $result = TOPDesk::getResolvedIncidentsForOperator('op-uuid');

    expect($result)->toBeArray()->toHaveKeys(['closed_day', 'closed_week', 'closed_month', 'closed_total', 'open']);
});

it('returns closed incidents breakdown for an operator', function () {
    Http::fake(['*api/incidents*' => Http::response([
        ['id' => 'inc-1', 'closed' => true, 'closedDate' => now()->toIso8601String()],
        ['id' => 'inc-2', 'closed' => true, 'closedDate' => now()->subDays(2)->toIso8601String()],
        ['id' => 'inc-3', 'closed' => false, 'closedDate' => null],
    ])]);

    $result = TOPDesk::getClosedIncidentsForOperator('op-uuid');

    expect($result)->toBeArray()
        ->toHaveKeys(['closed_day', 'closed_week', 'closed_month', 'closed_total', 'open']);
    expect($result['closed_total'])->toBe(2);
    expect($result['open'])->toBe(1);
});

it('getResolvedTicketsForOperator is an alias for getClosedIncidentsForOperator', function () {
    Http::fake(['*api/incidents*' => Http::response([
        ['id' => 'inc-1', 'closed' => true, 'closedDate' => now()->toIso8601String()],
    ])]);

    $result = TOPDesk::getResolvedTicketsForOperator('op-uuid');

    expect($result)->toBeArray()->toHaveKey('closed_total');
    expect($result['closed_total'])->toBe(1);
});
