<?php

declare(strict_types=1);

use Carbon\Carbon;
use FredBradley\TOPDesk\Exceptions\OperatorGroupNotFound;
use FredBradley\TOPDesk\Exceptions\OperatorNotFound;
use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('fetches a list of incidents with start=0 and page_size=100 by default', function () {
    Http::fake(['*api/incidents*' => Http::response([['id' => 'inc-1']])]);

    TOPDesk::getListOfIncidents();

    Http::assertSent(function ($r) {
        return str_contains($r->url(), 'api/incidents')
            && str_contains($r->url(), 'start=0')
            && str_contains($r->url(), 'page_size=100');
    });
});

it('merges caller options into the incident list request', function () {
    Http::fake(['*api/incidents*' => Http::response([])]);

    TOPDesk::getListOfIncidents(['page_size' => 25, 'status' => 'firstLine']);

    Http::assertSent(fn ($r) => str_contains($r->url(), 'page_size=25') &&
        str_contains($r->url(), 'status=firstLine')
    );
});

it('fetches an incident by UUID using the /id/ endpoint', function () {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    Http::fake(['*api/incidents/id/*' => Http::response(['id' => $uuid, 'number' => 'I-001'])]);

    $result = TOPDesk::getIncident($uuid);

    expect($result)->toBeObject()->and($result->id)->toBe($uuid);
    Http::assertSent(fn ($r) => str_contains($r->url(), "api/incidents/id/{$uuid}"));
});

it('fetches an incident by number using the /number/ endpoint', function () {
    Http::fake(['*api/incidents/number/*' => Http::response(['id' => 'abc', 'number' => 'I-2024-001'])]);

    $result = TOPDesk::getIncident('I-2024-001');

    expect($result->number)->toBe('I-2024-001');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents/number/I-2024-001'));
});

it('creates an incident via POST to api/incidents', function () {
    Http::fake(['*api/incidents*' => Http::response(['id' => 'new-id', 'number' => 'I-999'])]);

    $result = TOPDesk::createIncident(['briefDescription' => 'Printer broken']);

    expect($result)->toBeObject()->and($result->number)->toBe('I-999');
    Http::assertSent(function ($r) {
        $path = parse_url($r->url(), PHP_URL_PATH);

        return $r->method() === 'POST' && str_ends_with($path, '/api/incidents');
    });
});

it('updates an incident by uuid via PATCH to api/incidents/id/{id}', function () {
    $id = 'inc-uuid-123';
    Http::fake(['*api/incidents/id/*' => Http::response(['id' => $id])]);

    TOPDesk::updateIncident($id, ['briefDescription' => 'Updated']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), "api/incidents/id/{$id}")
    );
});

it('updates an incident by number via PATCH to api/incidents/number/{number}', function () {
    Http::fake(['*api/incidents/number/*' => Http::response(['number' => 'I-001'])]);

    TOPDesk::updateIncidentByNumber('I-001', ['briefDescription' => 'Updated']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH' &&
        str_contains($r->url(), 'api/incidents/number/I-001')
    );
});

it('returns all processing statuses as a Collection', function () {
    Http::fake(['*api/incidents/statuses*' => Http::response([
        ['id' => 'status-1', 'name' => 'Open'],
        ['id' => 'status-2', 'name' => 'Closed'],
    ])]);

    $result = TOPDesk::getAllProcessingStatuses();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
});

it('resolves a processing status id by its name', function () {
    Http::fake(['*api/incidents/statuses*' => Http::response([
        ['id' => 'status-open', 'name' => 'Open'],
        ['id' => 'status-closed', 'name' => 'Closed'],
    ])]);

    expect(TOPDesk::getProcessingStatusId('Open'))->toBe('status-open');
    expect(TOPDesk::getProcessingStatusId('Closed'))->toBe('status-closed');
});

it('resolves an operator group id by display name', function () {
    Http::fake(['*api/operatorgroups/lookup*' => Http::response([
        'results' => [['id' => 'group-uuid', 'name' => 'I.T. Services']],
    ])]);

    expect(TOPDesk::getOperatorGroupId('I.T. Services'))->toBe('group-uuid');
});

it('throws OperatorGroupNotFound when the group name does not exist', function () {
    Http::fake(['*api/operatorgroups/lookup*' => Http::response(['results' => []])]);

    expect(fn () => TOPDesk::getOperatorGroupId('Nonexistent'))
        ->toThrow(OperatorGroupNotFound::class);
});

it('returns a single operator as stdClass when found by username', function () {
    Http::fake(['*api/operators*' => Http::response([
        ['id' => 'op-uuid', 'networkLoginName' => 'jsmith'],
    ])]);

    $result = TOPDesk::getOperatorByUsername('jsmith');

    expect($result)->toBeObject()->and($result->networkLoginName)->toBe('jsmith');
});

it('throws OperatorNotFound when the operator lookup returns a null body', function () {
    // TOPdesk returns JSON null when no operator matches; object() returns null.
    Http::fake(['*api/operators*' => Http::response('null', 200, ['Content-Type' => 'application/json'])]);

    expect(fn () => TOPDesk::getOperatorByUsername('nobody'))
        ->toThrow(OperatorNotFound::class);
});

it('returns a Collection when getOperatorByUsername finds multiple matches', function () {
    Http::fake(['*api/operators*' => Http::response([
        ['id' => 'op-1', 'networkLoginName' => 'jsmith'],
        ['id' => 'op-2', 'networkLoginName' => 'jsmith2'],
    ])]);

    $result = TOPDesk::getOperatorByUsername('jsmith');

    // count > 1 returns the collection directly
    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
});

it('counts incidents matching a FIQL query', function () {
    Http::fake(['*api/incidents*' => Http::response([
        ['id' => 'inc-1'],
        ['id' => 'inc-2'],
    ])]);

    $count = TOPDesk::getNumIncidents('closed==false');

    expect($count)->toBe(2);
});

it('returns archive reasons as a Collection', function () {
    Http::fake(['*api/archiving-reasons*' => Http::response([
        ['id' => 'reason-1', 'name' => 'Duplicate'],
    ])]);

    $reasons = TOPDesk::getArchiveReasons();

    expect($reasons)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('resolves an archive reason id by name', function () {
    Http::fake(['*api/archiving-reasons*' => Http::response([
        ['id' => 'reason-dup', 'name' => 'Duplicate'],
        ['id' => 'reason-err', 'name' => 'Logged in error'],
    ])]);

    expect(TOPDesk::getArchiveReasonId('Duplicate'))->toBe('reason-dup');
});

it('getIncidentbyNumber (deprecated) delegates to getIncident by number', function () {
    Http::fake(['*api/incidents/number/*' => Http::response(['id' => 'inc-abc', 'number' => 'I-2024-001'])]);

    $result = TOPDesk::getIncidentbyNumber('I-2024-001');

    expect($result)->toBeObject()->and($result->number)->toBe('I-2024-001');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents/number/I-2024-001'));
});

it('returns a processing status object by name', function () {
    Http::fake(['*api/incidents/statuses*' => Http::response([
        ['id' => 'status-open', 'name' => 'Open'],
        ['id' => 'status-closed', 'name' => 'Closed'],
    ])]);

    $status = TOPDesk::getProcessingStatus('Open');

    expect($status)->toBeArray()->toHaveKey('id');
    expect($status['id'])->toBe('status-open');
});

it('throws an exception when a processing status name is not found', function () {
    Http::fake(['*api/incidents/statuses*' => Http::response([
        ['id' => 'status-open', 'name' => 'Open'],
    ])]);

    expect(fn () => TOPDesk::getProcessingStatus('Nonexistent'))
        ->toThrow(Exception::class, 'Status Not Found');
});

it('returns open incidents for an operator group as a Collection (default Open status)', function () {
    Http::fake([
        '*api/incidents/statuses*' => Http::response([
            ['id' => 'status-closed', 'name' => 'Closed'],
        ]),
        '*api/incidents*' => Http::response([
            ['id' => 'inc-1', 'creationDate' => now()->toIso8601String(), 'targetDate' => null],
            ['id' => 'inc-2', 'creationDate' => now()->toIso8601String(), 'targetDate' => null],
        ]),
    ]);

    $result = TOPDesk::getOpenIncidentsByOperatorGroupId('grp-uuid');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'grp-uuid')
    );
});

it('parses targetDate as a Carbon instance when it is not null', function () {
    Http::fake([
        '*api/incidents/statuses*' => Http::response([['id' => 'status-closed', 'name' => 'Closed']]),
        '*api/incidents*' => Http::response([
            [
                'id' => 'inc-1',
                'creationDate' => now()->toIso8601String(),
                'targetDate' => now()->addDays(3)->toIso8601String(),
            ],
        ]),
    ]);

    $result = TOPDesk::getOpenIncidentsByOperatorGroupId('grp-uuid');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    expect($result->first()->targetDate)->toBeInstanceOf(Carbon::class);
});

it('re-throws RequestException from getOpenIncidentsByOperatorGroupId when the API returns an error', function () {
    Http::fake([
        '*api/incidents/statuses*' => Http::response([['id' => 'status-closed', 'name' => 'Closed']]),
        '*api/incidents*' => Http::response(['error' => 'Server Error'], 500),
    ]);

    expect(fn () => TOPDesk::getOpenIncidentsByOperatorGroupId('grp-uuid'))
        ->toThrow(RequestException::class);
});

it('returns open incidents for an operator group with a specific processing status', function () {
    Http::fake([
        '*api/incidents/statuses*' => Http::response([
            ['id' => 'status-logged', 'name' => 'Logged'],
        ]),
        '*api/incidents*' => Http::response([
            ['id' => 'inc-1', 'creationDate' => now()->toIso8601String(), 'targetDate' => null],
        ]),
    ]);

    $result = TOPDesk::getOpenIncidentsByOperatorGroupId('grp-uuid', 'Logged');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('deprecatedgetOpenIncidentsByOperatorGroupId returns incidents with null processing status', function () {
    Http::fake(['*api/incidents*' => Http::response([['id' => 'inc-1'], ['id' => 'inc-2']])]);

    $result = TOPDesk::deprecatedgetOpenIncidentsByOperatorGroupId('grp-uuid');

    expect($result)->toBeArray();
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'closed%3D%3Dfalse') || str_contains($r->url(), 'closed==false')
    );
});

it('deprecatedgetOpenIncidentsByOperatorGroupId filters by processing status name', function () {
    Http::fake(['*api/incidents*' => Http::response([['id' => 'inc-1']])]);

    $result = TOPDesk::deprecatedgetOpenIncidentsByOperatorGroupId('grp-uuid', 'Logged', ['id', 'number']);

    expect($result)->toBeArray();
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents')
        && str_contains($r->url(), 'Logged')
    );
});
