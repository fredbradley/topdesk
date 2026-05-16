<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('returns branches as a Collection', function () {
    Http::fake(['*api/branches*' => Http::response([
        ['id' => 'br-1', 'name' => 'London HQ'],
        ['id' => 'br-2', 'name' => 'Manchester'],
    ])]);

    $result = TOPDesk::getBranches();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/branches'));
});

it('fetches a single branch by id via GET api/branches/id/{id}', function () {
    Http::fake(['*api/branches/id/*' => Http::response(['id' => 'br-1', 'name' => 'London HQ'])]);

    $result = TOPDesk::getBranch('br-1');

    expect($result)->toBeObject()->and($result->id)->toBe('br-1');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/branches/id/br-1'));
});

it('creates a branch via POST to api/branches', function () {
    Http::fake(['*api/branches*' => Http::response(['id' => 'br-new', 'name' => 'Birmingham'])]);

    $result = TOPDesk::createBranch(['name' => 'Birmingham']);

    expect($result)->toBeObject()->and($result->id)->toBe('br-new');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/api/branches'));
});

it('updates a branch via PATCH to api/branches/id/{id}', function () {
    Http::fake(['*api/branches/id/*' => Http::response(['id' => 'br-1'])]);

    TOPDesk::updateBranch('br-1', ['phone' => '+44 20 0000 0000']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/branches/id/br-1')
    );
});

it('archives a branch via PATCH to api/branches/id/{id}/archive', function () {
    Http::fake(['*archive*' => Http::response('', 204)]);

    TOPDesk::archiveBranch('br-1');

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/branches/id/br-1/archive')
    );
});

it('resolves a branch id by name from the lookup endpoint (cached)', function () {
    Http::fake(['*api/branches/lookup*' => Http::response([['id' => 'br-1', 'name' => 'London HQ']])]);

    $first = TOPDesk::getBranchId('London HQ');
    $second = TOPDesk::getBranchId('London HQ');

    expect($first)->toBe('br-1')->and($second)->toBe('br-1');
    Http::assertSentCount(1);
});

it('returns branch designations as a cached Collection', function () {
    Http::fake(['*api/branches/designations*' => Http::response([['id' => 'd-1', 'name' => 'Head Office']])]);

    $result = TOPDesk::getBranchDesignations();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('returns branch building levels as a cached Collection', function () {
    Http::fake(['*api/branches/buildingLevels*' => Http::response([['id' => 'bl-1', 'name' => 'Ground Floor']])]);

    $result = TOPDesk::getBranchBuildingLevels();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('returns branch attachments as a Collection', function () {
    Http::fake(['*api/branches/id/br-1/attachments*' => Http::response([['id' => 'att-1', 'fileName' => 'plan.pdf']])]);

    $result = TOPDesk::getBranchAttachments('br-1');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});
