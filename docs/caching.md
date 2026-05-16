# Caching

The package uses Laravel's `Cache` facade. Any cache driver configured in your Laravel application will be used automatically.

## Default TTLs

| Data type | TTL |
|---|---|
| Lookup lists (call types, impacts, etc.) | 1 week |
| Asset templates | 1 week |
| Asset template IDs | 30 days |
| Operator/person queries | 5 minutes |
| Incident counts | 5 minutes |
| Change activities | 10 minutes (open) / 1 hour (per-operator) |

## Busting the cache per call

Most methods that cache accept a `bool $forgetCache = false` parameter. Pass `true` to clear the cached entry and fetch fresh data:

```php
$types    = TOPDesk::getCallTypes(forgetCache: true);
$operator = TOPDesk::getOperatorByUsername('jsmith', forgetCache: true);
$groupId  = TOPDesk::getOperatorGroupId('I.T. Services', forgetCache: true);
```

## Disabling cache globally

Set the environment variable to disable caching entirely:

```env
TOPdesk_ignore_cache=true
```

When this is `true`, every call to `setupCacheObject()` clears the key before `Cache::remember()` runs, effectively making every request hit the API.

## How it works internally

The `setupCacheObject(string $cacheKey, bool $forgetCache): string` method is the single cache-busting entry point used by all traits. It:

1. Checks `$forgetCache` and the `topdesk.ignore_cache` config flag.
2. Calls `Cache::forget($cacheKey)` if either is true.
3. Returns the key unchanged — the calling code then passes it to `Cache::remember()`.

This means the forget-then-remember pattern is used throughout, rather than conditional logic scattered across traits.

## Cache keys

Keys are constructed from the endpoint and query variable names, slugified with `Str::slug()`. For example:

- `assetStatuses`
- `asset_templates`
- `get_operators_{operatorGroupId}`
- `operatorgroups-name-i-t-services`
