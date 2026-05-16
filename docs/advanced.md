# Advanced Usage

## FIQL query syntax

TOPdesk uses FIQL (Feed Item Query Language) for server-side filtering on list endpoints. Pass the filter string in the `query` key:

```php
TOPDesk::getListOfIncidents([
    'query' => '(processingStatus.name==Open);(operatorGroup.name==I.T. Services)',
]);
```

| Operator | Meaning | Example |
|---|---|---|
| `==` | Equals | `status.name==Open` |
| `!=` | Not equals | `status.name!=Closed` |
| `=in=` | In list | `status.name=in=(Open,InProgress)` |
| `=out=` | Not in list | `status.name=out=(Closed,Archived)` |
| `;` | AND | `status.name==Open;operatorGroup.name==IT` |
| `,` | OR | `status.name==Open,status.name==InProgress` |

Fields available for filtering depend on the endpoint — refer to the [TOPdesk API documentation](https://developers.topdesk.com/).

---

## Raw HTTP access

Use the HTTP primitives when you need an endpoint not covered by a named method:

```php
// GET with query parameters
$result = TOPDesk::get('api/incidents/call_types');

// POST with a JSON body
$result = TOPDesk::post('api/some/endpoint', ['key' => 'value']);

// PUT / PATCH / DELETE
TOPDesk::put('api/resource/'.$id, $data);
TOPDesk::patch('api/resource/'.$id, $data);
TOPDesk::delete('api/resource/'.$id);
```

All return a decoded `object`. A 204 No Content response returns an empty `array`. Any 4xx/5xx status throws `Illuminate\Http\Client\RequestException`.

### Access the underlying PendingRequest

`TOPDesk::query()` returns a pre-authenticated `PendingRequest` (base URL, basic auth, and `Accept: application/json` already set):

```php
// Multipart / file upload
$response = TOPDesk::query()
    ->attach('file', file_get_contents($path), 'report.pdf')
    ->post('api/incidents/'.$incidentId.'/attachments');

// Custom headers
$response = TOPDesk::query()
    ->withHeader('X-Custom', 'value')
    ->get('api/some/endpoint');

// Stream a response
TOPDesk::query()
    ->sink(storage_path('app/export.csv'))
    ->get('api/export');
```

---

## Models

Models provide a typed, cache-backed way to look up entities without knowing their API endpoint.

### Available models

| Model | Entity |
|---|---|
| `Asset` | Asset management assets |
| `Branch` | Branches |
| `Location` | Locations |
| `Operator` | Operators |
| `OperatorGroup` | Operator groups |
| `Person` | Persons |
| `PersonGroup` | Person groups |
| `Supplier` | Suppliers |

### Usage

```php
use FredBradley\TOPDesk\Models\Person;
use FredBradley\TOPDesk\Models\Operator;

// Fetch by UUID (5-minute cache)
$person = Person::findById($uuid);

// Shorthand alias for findById
$person = Person::find($uuid);

// Find the first match by any field
$operator = Operator::findFirstByVariable('networkLoginName', 'jsmith');

// Filtered collection — all matches
$people = Person::whereVariableEquals('surName', 'Smith');
// Returns: Collection of Person instances

// Force-refresh the cache
$person = Person::findById($uuid, forgetCache: true);
```

Properties are assigned dynamically from the API JSON response, so you access them as plain object properties:

```php
echo $person->id;
echo $person->surName;
echo $person->email;
```

---

## General API methods

```php
// TOPdesk API version string
$version = TOPDesk::getApiVersion();

// Full product version object
$product = TOPDesk::getProductVersion();

// Search across TOPdesk
$results = TOPDesk::search('printer jam', 'incidents', start: 0);
$results = TOPDesk::search('London', 'branches');

// Service windows
$windows = TOPDesk::getServiceWindows();
$window  = TOPDesk::getServiceWindow($windowId);

// Email items
$email = TOPDesk::getEmail($emailId);
TOPDesk::deleteEmail($emailId);
```

---

## Error handling

All HTTP errors surface as `Illuminate\Http\Client\RequestException`. Domain-specific exceptions are thrown before the HTTP call for lookup failures:

| Exception | When thrown |
|---|---|
| `ConfigNotFound` | A required config value is missing or empty at boot |
| `PersonNotFound` | `getPersonByUsername()` finds no match |
| `OperatorNotFound` | Operator lookup finds no match |
| `OperatorGroupNotFound` | `getOperatorGroupId()` finds no match |

```php
use FredBradley\TOPDesk\Exceptions\PersonNotFound;
use Illuminate\Http\Client\RequestException;

try {
    $person = TOPDesk::getPersonByUsername($username);
} catch (PersonNotFound $e) {
    // HTTP 404 — person not in TOPdesk
} catch (RequestException $e) {
    // 4xx/5xx from the API
    $statusCode = $e->response->status();
}
```

---

## Testing

The package uses Laravel's HTTP fake, so you can stub responses in tests without hitting the API:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    '*/api/persons*' => Http::response([
        ['id' => 'abc-123', 'surName' => 'Smith', 'networkLoginName' => 'jsmith'],
    ]),
]);

$person = TOPDesk::getPersonByUsername('jsmith');

Http::assertSent(fn ($request) => str_contains($request->url(), 'api/persons'));
```
