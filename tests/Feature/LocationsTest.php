<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

// getLocations and createLocation are already tested in GeneralTest.php.
// This file covers the remaining operations.

it('fetches a single location by id via GET api/locations/id/{id}', function () {
    Http::fake(['*api/locations/id/*' => Http::response(['id' => 'loc-1', 'name' => 'Server Room'])]);

    $result = TOPDesk::getLocation('loc-1');

    expect($result)->toBeObject()->and($result->id)->toBe('loc-1');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/locations/id/loc-1'));
});

it('updates a location via PATCH to api/locations/id/{id}', function () {
    Http::fake(['*api/locations/id/*' => Http::response(['id' => 'loc-1', 'name' => 'Updated Room'])]);

    $result = TOPDesk::updateLocation('loc-1', ['name' => 'Updated Room']);

    expect($result)->toBeObject()->and($result->name)->toBe('Updated Room');
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains($r->url(), 'api/locations/id/loc-1')
    );
});

it('archives a location via PATCH to api/locations/id/{id}/archive', function () {
    Http::fake(['*api/locations/id/*/archive*' => Http::response('', 204)]);

    $result = TOPDesk::archiveLocation('loc-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains($r->url(), 'api/locations/id/loc-1/archive')
    );
});

it('unarchives a location via PATCH to api/locations/id/{id}/unarchive', function () {
    Http::fake(['*api/locations/id/*/unarchive*' => Http::response(['id' => 'loc-1', 'name' => 'Server Room'])]);

    $result = TOPDesk::unarchiveLocation('loc-1');

    expect($result)->toBeObject()->and($result->id)->toBe('loc-1');
    Http::assertSent(fn ($r) => $r->method() === 'PATCH'
        && str_contains($r->url(), 'api/locations/id/loc-1/unarchive')
    );
});

it('returns location lookup results as a Collection', function () {
    Http::fake(['*api/locations/lookup*' => Http::response([
        ['id' => 'loc-1', 'name' => 'Server Room'],
        ['id' => 'loc-2', 'name' => 'Meeting Room A'],
    ])]);

    $result = TOPDesk::getLocationLookup(['name' => 'Room']);

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/locations/lookup'));
});

it('returns location types as a cached Collection', function () {
    Http::fake(['*api/locations/types*' => Http::response([
        ['id' => 'type-1', 'name' => 'Classroom'],
        ['id' => 'type-2', 'name' => 'Office'],
    ])]);

    $first = TOPDesk::getLocationTypes();
    $second = TOPDesk::getLocationTypes();

    expect($first)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSentCount(1); // cached after first call
});

it('returns location statuses as a cached Collection', function () {
    Http::fake(['*api/locations/statuses*' => Http::response([
        ['id' => 'status-1', 'name' => 'Available'],
    ])]);

    $result = TOPDesk::getLocationStatuses();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('returns building zones as a cached Collection', function () {
    Http::fake(['*api/locations/building_zones*' => Http::response([
        ['id' => 'zone-1', 'name' => 'North Wing'],
    ])]);

    $first = TOPDesk::getBuildingZones();
    $second = TOPDesk::getBuildingZones();

    expect($first)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSentCount(1);
});
