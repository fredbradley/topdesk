<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('fetches an operator by id via GET api/operators/id/{id}', function () {
    Http::fake(['*api/operators/id/*' => Http::response(['id' => 'op-uuid', 'surName' => 'Smith'])]);

    $result = TOPDesk::getOperatorById('op-uuid');

    expect($result)->toBeObject()->and($result->id)->toBe('op-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operators/id/op-uuid'));
});

it('sends a fields query param when specific fields are requested', function () {
    Http::fake(['*api/operators/id/*' => Http::response(['id' => 'op-uuid', 'email' => 'op@example.com'])]);

    TOPDesk::getOperatorById('op-uuid', ['id', 'email']);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'fields=id%2Cemail') || str_contains($r->url(), 'fields=id,email'));
});

it('creates an operator via POST to api/operators', function () {
    Http::fake(['*api/operators*' => Http::response(['id' => 'new-op', 'surName' => 'Doe'])]);

    $result = TOPDesk::createOperator(['surName' => 'Doe', 'firstName' => 'Jane']);

    expect($result)->toBeObject()->and($result->id)->toBe('new-op');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/api/operators'));
});

it('updates an operator via PATCH to api/operators/id/{id}', function () {
    Http::fake(['*api/operators/id/*' => Http::response(['id' => 'op-uuid'])]);

    TOPDesk::updateOperator('op-uuid', ['phoneNumber' => '+44 1234 000000']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/operators/id/op-uuid')
    );
});

it('archives an operator via PATCH to api/operators/id/{id}/archive', function () {
    Http::fake(['*archive*' => Http::response('', 204)]);

    TOPDesk::archiveOperator('op-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/operators/id/op-uuid/archive')
    );
});

it('fetches the current operator via GET api/operators/current', function () {
    Http::fake(['*api/operators/current*' => Http::response(['id' => 'me-uuid', 'surName' => 'Bradley'])]);

    $result = TOPDesk::getCurrentOperator();

    expect($result)->toBeObject()->and($result->id)->toBe('me-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operators/current'));
});

it('returns the current operator id as a string', function () {
    Http::fake(['*api/operators/current/id*' => Http::response('"me-uuid"')]);

    $result = TOPDesk::getCurrentOperatorId();

    expect($result)->toBe('me-uuid');
});

it('returns operator groups as a Collection', function () {
    Http::fake(['*api/operatorgroups*' => Http::response([
        ['id' => 'grp-1', 'groupName' => 'I.T. Services'],
    ])]);

    $result = TOPDesk::getOperatorGroups();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('fetches a single operator group by id', function () {
    Http::fake(['*api/operatorgroups/id/*' => Http::response(['id' => 'grp-1', 'groupName' => 'I.T. Services'])]);

    $result = TOPDesk::getOperatorGroupById('grp-1');

    expect($result)->toBeObject()->and($result->id)->toBe('grp-1');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/operatorgroups/id/grp-1'));
});

it('creates an operator group via POST', function () {
    Http::fake(['*api/operatorgroups*' => Http::response(['id' => 'new-grp', 'groupName' => 'Networking'])]);

    $result = TOPDesk::createOperatorGroup(['groupName' => 'Networking']);

    expect($result)->toBeObject()->and($result->id)->toBe('new-grp');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/api/operatorgroups'));
});

it('returns permission groups as a cached Collection', function () {
    Http::fake(['*api/permissiongroups*' => Http::response([['id' => 'perm-1', 'name' => 'Read-only']])]);

    $first = TOPDesk::getPermissionGroups();
    $second = TOPDesk::getPermissionGroups();

    expect($first)->toBeInstanceOf(Collection::class);
    expect($second)->toBeInstanceOf(Collection::class);
    Http::assertSentCount(1); // cached after first call
});

it('returns operators in a group as a Collection', function () {
    Http::fake(['*api/operatorgroups/id/grp-1/operators*' => Http::response([
        ['id' => 'op-1', 'networkLoginName' => 'jsmith'],
    ])]);

    $result = TOPDesk::getOperatorGroupOperators('grp-1');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});
