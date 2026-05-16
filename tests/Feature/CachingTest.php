<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Facades\TOPDesk;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('setupCacheObject returns the same key it receives', function () {
    $key = TOPDesk::setupCacheObject('my-cache-key', false);

    expect($key)->toBe('my-cache-key');
});

it('setupCacheObject clears the cache entry when forgetCache is true', function () {
    Cache::put('my-cache-key', 'cached-value', 3600);

    TOPDesk::setupCacheObject('my-cache-key', forgetCache: true);

    expect(Cache::has('my-cache-key'))->toBeFalse();
});

it('setupCacheObject leaves the cache entry intact when forgetCache is false', function () {
    Cache::put('my-cache-key', 'cached-value', 3600);

    TOPDesk::setupCacheObject('my-cache-key', forgetCache: false);

    expect(Cache::get('my-cache-key'))->toBe('cached-value');
});

it('setupCacheObject clears the cache when ignore_cache config is true', function () {
    config(['topdesk.ignore_cache' => true]);
    Cache::put('my-cache-key', 'cached-value', 3600);

    TOPDesk::setupCacheObject('my-cache-key', forgetCache: false);

    expect(Cache::has('my-cache-key'))->toBeFalse();
});

it('cached methods make only one HTTP call on repeated invocations', function () {
    Http::fake(['*api/incidents/priorities*' => Http::response([['id' => 'p-1', 'name' => 'High']])]);

    TOPDesk::getPriorities();
    TOPDesk::getPriorities();
    TOPDesk::getPriorities();

    Http::assertSentCount(1);
});

it('passing forgetCache = true causes the method to re-fetch from the API', function () {
    Http::fake(['*api/incidents/urgencies*' => Http::response([['id' => 'u-1', 'name' => 'High']])]);

    TOPDesk::getUrgencies();          // 1st call: hits API, caches
    TOPDesk::getUrgencies(true);     // 2nd call: busts cache, hits API again
    TOPDesk::getUrgencies();          // 3rd call: hits cache (repopulated by 2nd)

    Http::assertSentCount(2);
});

it('forgetCache = true on getOperatorGroupId forces a re-fetch', function () {
    Http::fake(['*api/operatorgroups/lookup*' => Http::response([
        'results' => [['id' => 'grp-1', 'name' => 'I.T. Services']],
    ])]);

    TOPDesk::getOperatorGroupId('I.T. Services');
    TOPDesk::getOperatorGroupId('I.T. Services', forgetCache: true);

    Http::assertSentCount(2);
});
