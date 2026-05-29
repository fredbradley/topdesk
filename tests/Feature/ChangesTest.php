<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('returns all open change activities as a Collection', function () {
    Http::fake(['*api/operatorChangeActivities*' => Http::response([
        'results' => [
            ['id' => 'ca-1', 'title' => 'Change 1'],
            ['id' => 'ca-2', 'title' => 'Change 2'],
        ],
    ])]);

    $result = TOPDesk::allOpenChangeActivities();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorChangeActivities')
        && str_contains($r->url(), 'open=true')
        && str_contains($r->url(), 'blocked=false')
    );
});

it('returns an empty Collection when open change activities results are null', function () {
    Http::fake(['*api/operatorChangeActivities*' => Http::response(['results' => null])]);

    $result = TOPDesk::allOpenChangeActivities();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(0);
});

it('caches allOpenChangeActivities and only calls the API once', function () {
    Http::fake(['*api/operatorChangeActivities*' => Http::response(['results' => [['id' => 'ca-1']]])]);

    TOPDesk::allOpenChangeActivities();
    TOPDesk::allOpenChangeActivities();

    Http::assertSentCount(1);
});

it('returns unassigned waiting change activities filtered by operator group', function () {
    Http::fake([
        '*api/operatorgroups/lookup*' => Http::response(['results' => [['id' => 'grp-uuid', 'name' => 'I.T. Services']]]),
        '*api/operatorChangeActivities*' => Http::response(['results' => [['id' => 'ca-2', 'title' => 'Waiting change']]]),
    ]);

    $result = TOPDesk::unassignedWaitingChangeActivities('I.T. Services');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorChangeActivities')
        && str_contains($r->url(), 'operator=grp-uuid')
    );
});

it('returns waiting change activities by username', function () {
    Http::fake([
        '*api/operators*' => Http::response([['id' => 'op-uuid', 'networkLoginName' => 'jsmith']]),
        '*api/operatorChangeActivities*' => Http::response(['results' => [['id' => 'ca-3', 'title' => 'Server update']]]),
    ]);

    $result = TOPDesk::waitingChangeActivitiesByUsername('jsmith');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorChangeActivities')
        && str_contains($r->url(), 'operator=op-uuid')
    );
});

it('returns resolved change activities by operator id and time string', function () {
    Http::fake(['*api/operatorChangeActivities*' => Http::response(['results' => [
        ['id' => 'ca-4'],
        ['id' => 'ca-5'],
    ]])]);

    $result = TOPDesk::resolvedChangeActivitiesByOperatorIdByTime('op-uuid', 'Week');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorChangeActivities')
        && str_contains($r->url(), 'open=false')
        && str_contains($r->url(), 'operator=op-uuid')
    );
});

it('returns waiting change activities by operator id directly', function () {
    Http::fake(['*api/operatorChangeActivities*' => Http::response(['results' => [
        ['id' => 'ca-6'],
        ['id' => 'ca-7'],
        ['id' => 'ca-8'],
    ]])]);

    $result = TOPDesk::waitingChangeActivitiesByOperatorId('op-uuid');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(3);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorChangeActivities')
        && str_contains($r->url(), 'open=true')
        && str_contains($r->url(), 'operator=op-uuid')
    );
});
