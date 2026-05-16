<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

dataset('incident_lookup_methods', [
    'call types'           => ['getCallTypes',          'api/incidents/call_types'],
    'durations'            => ['getDurations',           'api/incidents/durations'],
    'entry types'          => ['getEntryTypes',          'api/incidents/entry_types'],
    'impacts'              => ['getImpacts',             'api/incidents/impacts'],
    'priorities'           => ['getPriorities',          'api/incidents/priorities'],
    'urgencies'            => ['getUrgencies',           'api/incidents/urgencies'],
    'closure codes'        => ['getClosureCodes',        'api/incidents/closure_codes'],
    'escalation reasons'   => ['getEscalationReasons',   'api/incidents/escalation-reasons'],
    'deescalation reasons' => ['getDeescalationReasons', 'api/incidents/deescalation-reasons'],
    'time spent reasons'   => ['getTimeSpentReasons',    'api/timespent-reasons'],
]);

it('returns lookup list as a Collection from the correct endpoint', function (string $method, string $urlPath) {
    Http::fake(["*{$urlPath}*" => Http::response([['id' => '1', 'name' => 'Test']])]);

    $result = TOPDesk::$method();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
    Http::assertSent(fn ($r) => str_contains($r->url(), $urlPath));
})->with('incident_lookup_methods');

it('caches lookup results — HTTP is called only once on repeated calls', function () {
    Http::fake(['*call_types*' => Http::response([['id' => '1', 'name' => 'Phone']])]);

    TOPDesk::getCallTypes();
    TOPDesk::getCallTypes();

    Http::assertSentCount(1);
});

it('re-fetches after the cache is busted with forgetCache = true', function () {
    Http::fake(['*call_types*' => Http::response([['id' => '1', 'name' => 'Phone']])]);

    TOPDesk::getCallTypes();
    TOPDesk::getCallTypes(forgetCache: true);

    Http::assertSentCount(2);
});

it('returns SLAs as a Collection', function () {
    Http::fake(['*api/incidents/slas*' => Http::response([['id' => 'sla-1', 'name' => '4h fix']])]);

    $result = TOPDesk::getSlas();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(1);
});

it('returns SLA services as a Collection', function () {
    Http::fake(['*api/incidents/slas/services*' => Http::response([['id' => 'svc-1']])]);

    $result = TOPDesk::getSlaServices();

    expect($result)->toBeInstanceOf(Collection::class);
});

it('returns categories as a Collection', function () {
    Http::fake(['*api/categories*' => Http::response([
        ['id' => 'cat-1', 'name' => 'Hardware'],
        ['id' => 'cat-2', 'name' => 'Software'],
    ])]);

    $result = TOPDesk::getCategories();

    expect($result)->toBeInstanceOf(Collection::class)->toHaveCount(2);
});
