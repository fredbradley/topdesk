# Upgrade Guide: 3.x → 4.x

## Requirements

| | 3.x | 4.x |
|---|---|---|
| PHP | ^8.0 | ^8.2 |
| Laravel | ~9\|~10\|~11\|~12 | ~9\|~10\|~11\|~12\|~13 |

---

## Removed methods

### `TOPDesk::request()`

This method was already marked deprecated and now throws on every call. It has been removed entirely.

```php
// before
TOPDesk::request('GET', 'api/...', $body, $query, $options);

// after — use the explicit HTTP verb methods
TOPDesk::get('api/...', $query);
TOPDesk::post('api/...', $body);
TOPDesk::put('api/...', $body);
TOPDesk::patch('api/...', $body);
TOPDesk::delete('api/...', $body);
```

---

## Changed method signatures

### `OperatorStats::resolveCountsForOperatorGroup()`

The second parameter was renamed from `$ignoreUsername` (singular) to `$ignoreUsernames` (plural). If you are passing this argument **by name** it will break; positional callers are unaffected.

```php
// before (named argument)
TOPDesk::resolveCountsForOperatorGroup('I.T. Services', ignoreUsername: ['jsmith']);

// after
TOPDesk::resolveCountsForOperatorGroup('I.T. Services', ignoreUsernames: ['jsmith']);
```

### `Incidents::getOperatorByUsername()`

`$forgetCache` is now declared `bool`. PHP will coerce most values silently, but strict-type callers passing non-boolean values should update.

```php
// before
TOPDesk::getOperatorByUsername('jsmith', 1);

// after
TOPDesk::getOperatorByUsername('jsmith', true);
```

---

## Changed return types

The following methods previously returned a plain `array` and now return an `Illuminate\Support\Collection`. Most array operations (`count()`, `foreach`, indexed access) continue to work on Collections, but strict `array` type hints in calling code will break.

| Method | Old return | New return |
|---|---|---|
| `Changes::allOpenChangeActivities()` | `array` | `Collection` |
| `Changes::unassignedWaitingChangeActivities()` | `array` | `Collection` |
| `Changes::waitingChangeActivitiesByUsername()` | `array` | `Collection` |
| `Changes::resolvedChangeActivitiesByOperatorIdByTime()` | `array` | `Collection` |
| `Changes::waitingChangeActivitiesByOperatorId()` | `array` | `Collection` |
| `OperatorStats::getOperatorsByOperatorGroup()` | `array` | `Collection` |

**Migration:** anywhere you type-hint `array` for these results, change to `\Illuminate\Support\Collection` or remove the type hint.

---

## Changed exception behaviour

### `Persons::getPersonByUsername()` — exception type

Now throws `FredBradley\TOPDesk\Exceptions\PersonNotFound` instead of the generic `\Exception`.

```php
// before
try {
    TOPDesk::getPersonByUsername('jsmith');
} catch (\Exception $e) {
    // caught generic Exception
}

// after — catch the specific exception (still extends \Exception, so existing
// bare \Exception catches continue to work, but prefer the specific type)
use FredBradley\TOPDesk\Exceptions\PersonNotFound;

try {
    TOPDesk::getPersonByUsername('jsmith');
} catch (PersonNotFound $e) {
    // HTTP 404-equivalent
}
```

### `ConfigNotFound` — base class changed

`ConfigNotFound` now extends `\RuntimeException` instead of `\Exception`. Both share the same ancestor, so existing `catch (\Exception $e)` blocks still catch it.

---

## Changed API endpoints

### `Persons::getPersonById()`

The internal endpoint changed from the legacy `/api/persons/id/{id}` pattern to `/api/persons/{id}` (TOPdesk v2 persons API). This is transparent to callers of the public method but affects any code that constructs URLs manually.

---

## Removed dependency: `fredbradley/cacher`

The package no longer depends on `fredbradley/cacher`. All caching now goes through Laravel's built-in `Illuminate\Support\Facades\Cache`. If your application referenced `FredBradley\Cacher\Cacher` directly (or relied on it being present transitively), add it to your own `composer.json`.

---

## Test suite: PHPUnit → Pest

The dev dependency has changed from `phpunit/phpunit` to `pestphp/pest`. If you extend the package's test helpers or run tests in a pipeline, update your tooling accordingly. The `composer test` script now runs `./vendor/bin/pest`.
