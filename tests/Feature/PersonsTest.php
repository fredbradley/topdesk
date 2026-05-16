<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Exceptions\PersonNotFound;
use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('returns a person as stdClass when found by username', function () {
    Http::fake(['*api/persons*' => Http::response([
        ['id' => 'person-uuid', 'surName' => 'Smith', 'networkLoginName' => 'jsmith'],
    ])]);

    $result = TOPDesk::getPersonByUsername('jsmith');

    expect($result)->toBeObject()
        ->and($result->networkLoginName)->toBe('jsmith')
        ->and($result->surName)->toBe('Smith');
});

it('throws PersonNotFound when the username returns an empty result', function () {
    Http::fake(['*api/persons*' => Http::response([])]);

    expect(fn () => TOPDesk::getPersonByUsername('nobody'))
        ->toThrow(PersonNotFound::class);
});

it('fetches a person by UUID via GET api/persons/{id}', function () {
    Http::fake(['*api/persons/*' => Http::response(['id' => 'person-uuid', 'surName' => 'Doe'])]);

    $result = TOPDesk::getPersonById('person-uuid');

    expect($result)->toBeObject()->and($result->id)->toBe('person-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/persons/person-uuid'));
});

it('creates a person via POST to api/persons', function () {
    Http::fake(['*api/persons*' => Http::response(['id' => 'new-person', 'surName' => 'Jones'])]);

    $result = TOPDesk::createPerson(['surName' => 'Jones', 'firstName' => 'Dave']);

    expect($result)->toBeObject()->and($result->id)->toBe('new-person');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with(parse_url($r->url(), PHP_URL_PATH), '/api/persons'));
});

it('updates a person via PATCH to api/persons/{id}', function () {
    Http::fake(['*api/persons/*' => Http::response(['id' => 'person-uuid'])]);

    TOPDesk::updatePerson('person-uuid', ['phoneNumber' => '+44 1234 567890']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/persons/person-uuid')
    );
});

it('archives a person via PATCH to api/persons/id/{id}/archive', function () {
    Http::fake(['*archive*' => Http::response('', 204)]);

    TOPDesk::archivePerson('person-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/persons/id/person-uuid/archive')
    );
});

it('unarchives a person via PATCH to api/persons/id/{id}/unarchive', function () {
    Http::fake(['*unarchive*' => Http::response('', 204)]);

    TOPDesk::unarchivePerson('person-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'unarchive'));
});

it('fetches the current person via GET api/persons/current', function () {
    Http::fake(['*api/persons/current*' => Http::response(['id' => 'me-uuid', 'surName' => 'Bradley'])]);

    $result = TOPDesk::getCurrentPerson();

    expect($result)->toBeObject()->and($result->id)->toBe('me-uuid');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/persons/current'));
});

it('returns the person count as an integer', function () {
    Http::fake(['*api/persons/count*' => Http::response(42)]);

    $result = TOPDesk::getPersonCount();

    expect($result)->toBeInt()->toBe(42);
});

it('returns person groups as a Collection', function () {
    Http::fake(['*api/persongroups*' => Http::response([
        ['id' => 'pg-1', 'groupName' => 'Finance'],
        ['id' => 'pg-2', 'groupName' => 'IT'],
    ])]);

    $result = TOPDesk::getPersonGroups();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
});

it('fetches a single person group by id', function () {
    Http::fake(['*api/persongroups/pg-1*' => Http::response(['id' => 'pg-1', 'groupName' => 'Finance'])]);

    $result = TOPDesk::getPersonGroup('pg-1');

    expect($result)->toBeObject()->and($result->id)->toBe('pg-1');
});

it('creates a person group via POST', function () {
    Http::fake(['*api/persongroups*' => Http::response(['id' => 'new-pg', 'groupName' => 'Marketing'])]);

    $result = TOPDesk::createPersonGroup(['groupName' => 'Marketing']);

    expect($result)->toBeObject()->and($result->id)->toBe('new-pg');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), 'api/persongroups'));
});

it('returns members of a person group as a Collection', function () {
    Http::fake(['*api/persongroups/pg-1/persons*' => Http::response([['id' => 'person-1']])]);

    $result = TOPDesk::getPersonGroupPersons('pg-1');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('returns groups that a person belongs to as a Collection', function () {
    Http::fake(['*api/persons/person-uuid/persongroups*' => Http::response([['id' => 'pg-1']])]);

    $result = TOPDesk::getPersonGroupsForPerson('person-uuid');

    expect($result)->toBeInstanceOf(Collection::class);
});
