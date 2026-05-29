<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

const INCIDENT_ID = '550e8400-e29b-41d4-a716-446655440000';

it('returns the progress trail as a Collection', function () {
    Http::fake(['*progresstrail*' => Http::response([['id' => 'trail-1', 'memoText' => 'First update']])]);

    $result = TOPDesk::getProgressTrail(INCIDENT_ID);

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'id/'.INCIDENT_ID.'/progresstrail'));
});

it('fetches progress trail by incident number', function () {
    Http::fake(['*progresstrail*' => Http::response([])]);

    TOPDesk::getProgressTrailByNumber('I-001');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/progresstrail'));
});

it('returns the progress trail count as an integer', function () {
    Http::fake(['*progresstrail/count*' => Http::response(7)]);

    $result = TOPDesk::getProgressTrailCount(INCIDENT_ID);

    expect($result)->toBeInt()->toBe(7);
});

it('returns incident actions as a Collection', function () {
    Http::fake(['*actions*' => Http::response([['id' => 'act-1', 'memoText' => 'Investigated issue']])]);

    $result = TOPDesk::getIncidentActions(INCIDENT_ID);

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'id/'.INCIDENT_ID.'/actions'));
});

it('fetches incident actions by incident number', function () {
    Http::fake(['*actions*' => Http::response([])]);

    TOPDesk::getIncidentActionsByNumber('I-001');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/actions'));
});

it('fetches a single incident action by id', function () {
    Http::fake(['*actions/act-1*' => Http::response(['id' => 'act-1', 'memoText' => 'note'])]);

    $result = TOPDesk::getIncidentAction(INCIDENT_ID, 'act-1');

    expect($result)->toBeObject()->and($result->id)->toBe('act-1');
});

it('deletes an incident action via DELETE', function () {
    Http::fake(['*actions/act-1*' => Http::response('', 204)]);

    $result = TOPDesk::deleteIncidentAction(INCIDENT_ID, 'act-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_contains($r->url(), 'actions/act-1'));
});

it('returns incident requests as a Collection', function () {
    Http::fake(['*requests*' => Http::response([['id' => 'req-1', 'memoText' => 'What is wrong?']])]);

    $result = TOPDesk::getIncidentRequests(INCIDENT_ID);

    expect($result)->toBeInstanceOf(Collection::class);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'id/'.INCIDENT_ID.'/requests'));
});

it('returns time spent entries as a Collection', function () {
    Http::fake(['*timespent*' => Http::response([['id' => 'ts-1', 'timeSpent' => 30]])]);

    $result = TOPDesk::getTimeSpent(INCIDENT_ID);

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'id/'.INCIDENT_ID.'/timespent'));
});

it('registers time spent via POST and returns the entry', function () {
    Http::fake(['*timespent*' => Http::response(['id' => 'ts-new', 'timeSpent' => 30])]);

    $result = TOPDesk::registerTimeSpent(INCIDENT_ID, ['timeSpent' => 30]);

    expect($result)->toBeObject()->and($result->id)->toBe('ts-new');
    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), 'id/'.INCIDENT_ID.'/timespent'));
});

it('registers time spent by incident number', function () {
    Http::fake(['*timespent*' => Http::response(['id' => 'ts-new'])]);

    TOPDesk::registerTimeSpentByNumber('I-001', ['timeSpent' => 15]);

    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), 'number/I-001/timespent'));
});

it('escalates an incident by id via PUT', function () {
    Http::fake(['*escalate*' => Http::response(['id' => INCIDENT_ID])]);

    TOPDesk::escalateIncident(INCIDENT_ID, 'reason-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'PUT' &&
        str_contains($r->url(), 'id/'.INCIDENT_ID.'/escalate')
    );
});

it('escalates an incident by number via PUT', function () {
    Http::fake(['*escalate*' => Http::response(['number' => 'I-001'])]);

    TOPDesk::escalateIncidentByNumber('I-001', 'reason-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/escalate'));
});

it('de-escalates an incident by id via PUT', function () {
    Http::fake(['*deescalate*' => Http::response(['id' => INCIDENT_ID])]);

    TOPDesk::deescalateIncident(INCIDENT_ID, 'reason-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'PUT' && str_contains($r->url(), 'deescalate'));
});

it('archives an incident by id via PUT with a reason id', function () {
    Http::fake(['*archive*' => Http::response(['id' => INCIDENT_ID])]);

    TOPDesk::archiveIncident(INCIDENT_ID, 'reason-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'PUT' &&
        str_contains($r->url(), 'id/'.INCIDENT_ID.'/archive')
    );
});

it('archives an incident by number', function () {
    Http::fake(['*archive*' => Http::response(['number' => 'I-001'])]);

    TOPDesk::archiveIncidentByNumber('I-001', 'reason-uuid');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/archive'));
});

it('unarchives an incident by id via PUT', function () {
    Http::fake(['*unarchive*' => Http::response(['id' => INCIDENT_ID])]);

    TOPDesk::unarchiveIncident(INCIDENT_ID);

    Http::assertSent(fn ($r) => $r->method() === 'PUT' && str_contains($r->url(), 'unarchive'));
});

it('returns incident attachments as a Collection', function () {
    Http::fake(['*attachments*' => Http::response([['id' => 'att-1', 'fileName' => 'report.pdf']])]);

    $result = TOPDesk::getIncidentAttachments(INCIDENT_ID);

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'id/'.INCIDENT_ID.'/attachments'));
});

it('fetches incident attachments by number', function () {
    Http::fake(['*attachments*' => Http::response([])]);

    TOPDesk::getIncidentAttachmentsByNumber('I-001');

    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/attachments'));
});

it('deletes an incident attachment via DELETE', function () {
    Http::fake(['*attachments/att-1*' => Http::response('', 204)]);

    $result = TOPDesk::deleteIncidentAttachment(INCIDENT_ID, 'att-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'DELETE'
        && str_contains($r->url(), 'attachments/att-1')
    );
});

it('returns the progress trail count by incident number as an integer', function () {
    Http::fake(['*progresstrail/count*' => Http::response(3)]);

    $result = TOPDesk::getProgressTrailCountByNumber('I-001');

    expect($result)->toBeInt()->toBe(3);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/progresstrail/count'));
});

it('returns incident requests by number as a Collection', function () {
    Http::fake(['*requests*' => Http::response([['id' => 'req-1', 'memoText' => 'Request text']])]);

    $result = TOPDesk::getIncidentRequestsByNumber('I-001');

    expect($result)->toBeInstanceOf(Collection::class);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/requests'));
});

it('fetches a single incident request by id', function () {
    Http::fake(['*requests/req-1*' => Http::response(['id' => 'req-1', 'memoText' => 'Original request'])]);

    $result = TOPDesk::getIncidentRequest(INCIDENT_ID, 'req-1');

    expect($result)->toBeObject()->and($result->id)->toBe('req-1');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'id/'.INCIDENT_ID.'/requests/req-1'));
});

it('deletes an incident request via DELETE', function () {
    Http::fake(['*requests/req-1*' => Http::response('', 204)]);

    $result = TOPDesk::deleteIncidentRequest(INCIDENT_ID, 'req-1');

    expect($result)->toBe([]);
    Http::assertSent(fn ($r) => $r->method() === 'DELETE'
        && str_contains($r->url(), 'id/'.INCIDENT_ID.'/requests/req-1')
    );
});

it('returns time spent entries by incident number as a Collection', function () {
    Http::fake(['*timespent*' => Http::response([['id' => 'ts-1', 'timeSpent' => 45]])]);

    $result = TOPDesk::getTimeSpentByNumber('I-001');

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'number/I-001/timespent'));
});

it('returns time registrations as a Collection', function () {
    Http::fake(['*timeregistrations*' => Http::response([
        ['id' => 'tr-1', 'timeSpent' => 60],
        ['id' => 'tr-2', 'timeSpent' => 30],
    ])]);

    $result = TOPDesk::getTimeRegistrations();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents/timeregistrations'));
});

it('fetches a single time registration by id', function () {
    Http::fake(['*timeregistrations/tr-1*' => Http::response(['id' => 'tr-1', 'timeSpent' => 60])]);

    $result = TOPDesk::getTimeRegistration('tr-1');

    expect($result)->toBeObject()->and($result->id)->toBe('tr-1');
    Http::assertSent(fn ($r) => str_contains($r->url(), 'api/incidents/timeregistrations/tr-1'));
});

it('de-escalates an incident by number via PUT', function () {
    Http::fake(['*deescalate*' => Http::response(['number' => 'I-001'])]);

    TOPDesk::deescalateIncidentByNumber('I-001', 'reason-uuid');

    Http::assertSent(fn ($r) => $r->method() === 'PUT'
        && str_contains($r->url(), 'number/I-001/deescalate')
    );
});

it('unarchives an incident by number via PUT', function () {
    Http::fake(['*unarchive*' => Http::response(['number' => 'I-001'])]);

    TOPDesk::unarchiveIncidentByNumber('I-001');

    Http::assertSent(fn ($r) => $r->method() === 'PUT'
        && str_contains($r->url(), 'number/I-001/unarchive')
    );
});
