<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('dispatches a GET request to the correct URL', function () {
    Http::fake(['*api/version*' => Http::response(['version' => '1.0'])]);

    TOPDesk::get('api/version');

    Http::assertSent(fn ($r) => $r->method() === 'GET' && str_contains($r->url(), 'api/version'));
});

it('dispatches a POST request with a JSON body', function () {
    Http::fake(['*api/incidents*' => Http::response(['id' => 'new-id', 'number' => 'I-001'])]);

    TOPDesk::post('api/incidents', ['briefDescription' => 'Test']);

    Http::assertSent(fn ($r) => $r->method() === 'POST' && str_contains($r->url(), 'api/incidents'));
});

it('dispatches a PUT request', function () {
    Http::fake(['*' => Http::response(['id' => 'abc'])]);

    TOPDesk::put('api/incidents/id/abc/escalate', ['id' => 'reason-id']);

    Http::assertSent(fn ($r) => $r->method() === 'PUT' && str_contains($r->url(), 'escalate'));
});

it('dispatches a PATCH request', function () {
    Http::fake(['*' => Http::response(['id' => 'abc'])]);

    TOPDesk::patch('api/incidents/id/abc', ['briefDescription' => 'Updated']);

    Http::assertSent(fn ($r) => $r->method() === 'PATCH');
});

it('dispatches a DELETE request', function () {
    Http::fake(['*' => Http::response('', 204)]);

    TOPDesk::delete('api/incidents/id/abc/actions/act-id');

    Http::assertSent(fn ($r) => $r->method() === 'DELETE');
});

it('returns an empty array for 204 No Content responses', function () {
    Http::fake(['*' => Http::response('', 204)]);

    $result = TOPDesk::delete('api/some/resource/id');

    expect($result)->toBe([]);
});

it('throws RequestException for 4xx responses', function () {
    Http::fake(['*' => Http::response(['message' => 'Not found'], 404)]);

    expect(fn () => TOPDesk::get('api/incidents/id/nonexistent'))
        ->toThrow(RequestException::class);
});

it('sends basic auth and Accept: application/json on every request', function () {
    Http::fake(['*' => Http::response(['version' => '1.0'])]);

    TOPDesk::get('api/version');

    Http::assertSent(function ($r) {
        return $r->hasHeader('Authorization')
            && $r->header('Accept')[0] === 'application/json'
            && str_starts_with($r->url(), 'https://company.topdesk.net/tas/');
    });
});

it('returns a decoded object from a JSON object response', function () {
    Http::fake(['*' => Http::response(['id' => 'abc', 'number' => 'I-001'])]);

    $result = TOPDesk::get('api/incidents/id/abc');

    expect($result)->toBeObject()
        ->and($result->id)->toBe('abc')
        ->and($result->number)->toBe('I-001');
});
