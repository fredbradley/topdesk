<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('returns the API version as a string', function () {
    Http::fake(['*api/version*' => Http::response(['version' => '1.2.3'])]);

    $result = TOPDesk::getApiVersion();

    expect($result)->toBeString()->toBe('1.2.3');
});

it('returns the full product version as an object', function () {
    Http::fake(['*api/productVersion*' => Http::response(['version' => '10.15.0', 'patchLevel' => '001'])]);

    $result = TOPDesk::getProductVersion();

    expect($result)->toBeObject()->and($result->version)->toBe('10.15.0');
});

it('returns search results as a Collection', function () {
    Http::fake(['*api/search*' => Http::response([
        'results' => [
            ['id' => 'inc-1', 'number' => 'I-001'],
            ['id' => 'inc-2', 'number' => 'I-002'],
        ],
    ])]);

    $result = TOPDesk::search('printer jam');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/search'));
});

it('returns an empty Collection when search has no results', function () {
    Http::fake(['*api/search*' => Http::response(['results' => []])]);

    $result = TOPDesk::search('no results for this query');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(0);
});

it('passes the index and start parameters to the search endpoint', function () {
    Http::fake(['*api/search*' => Http::response(['results' => []])]);

    TOPDesk::search('office', 'branches', 10);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'index=branches') &&
        str_contains($r->url(), 'start=10')
    );
});

it('returns countries as a cached Collection', function () {
    Http::fake(['*api/countries*' => Http::response([
        ['id' => 'c-1', 'name' => 'United Kingdom'],
    ])]);

    $first = TOPDesk::getCountries();
    $second = TOPDesk::getCountries();

    expect($first)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSentCount(1);
});

it('returns languages as a cached Collection', function () {
    Http::fake(['*api/languages*' => Http::response([
        ['id' => 'lang-1', 'name' => 'English'],
    ])]);

    $result = TOPDesk::getLanguages();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('fetches departments as a Collection', function () {
    Http::fake(['*api/departments*' => Http::response([
        ['id' => 'dept-1', 'name' => 'Finance'],
    ])]);

    $result = TOPDesk::getDepartments();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('creates a department via POST with just a name', function () {
    Http::fake(['*api/departments*' => Http::response(['id' => 'dept-new', 'name' => 'Marketing'])]);

    $result = TOPDesk::createDepartment('Marketing');

    expect($result)->toBeObject()->and($result->id)->toBe('dept-new');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), 'api/departments'));
});

it('fetches suppliers as a Collection', function () {
    Http::fake(['*api/suppliers*' => Http::response([
        ['id' => 'sup-1', 'name' => 'Dell'],
    ])]);

    $result = TOPDesk::getSuppliers();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('fetches a single supplier by id', function () {
    Http::fake(['*api/suppliers/*' => Http::response(['id' => 'sup-1', 'name' => 'Dell'])]);

    $result = TOPDesk::getSupplier('sup-1');

    expect($result)->toBeObject()->and($result->id)->toBe('sup-1');
});

it('fetches locations as a Collection', function () {
    Http::fake(['*api/locations*' => Http::response([
        ['id' => 'loc-1', 'name' => 'Floor 3'],
    ])]);

    $result = TOPDesk::getLocations();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('creates a location via POST to api/locations', function () {
    Http::fake(['*api/locations*' => Http::response(['id' => 'loc-new', 'name' => 'Floor 4'])]);

    $result = TOPDesk::createLocation(['name' => 'Floor 4', 'branch' => ['id' => 'br-1']]);

    expect($result)->toBeObject()->and($result->id)->toBe('loc-new');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/api/locations'));
});
