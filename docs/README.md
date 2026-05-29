# TOPDesk Laravel Package — Documentation

A Laravel wrapper for the TOPdesk REST API. Supports Laravel 9–13 and PHP 8.2+.

## Contents

| Document | What it covers |
|---|---|
| [Getting Started](getting-started.md) | Installation, configuration, basic usage |
| [Incidents](incidents.md) | Create, update, list, actions, time, escalation, archiving, lookup lists |
| [People](people.md) | Person lookups, create/update, person groups |
| [Operators](operators.md) | Operator lookups, create/update, operator groups, per-operator stats |
| [Assets](assets.md) | Asset CRUD, assignments, links, history, lookup lists |
| [Supporting Files](supporting-files.md) | Branches, locations, departments, suppliers, countries, languages |
| [Change Management](changes.md) | Change activities — open, resolved, per-operator |
| [Statistics](statistics.md) | Counts and breakdowns for dashboards |
| [Caching](caching.md) | Cache TTLs, per-call cache busting, global disable |
| [Advanced](advanced.md) | FIQL syntax, raw HTTP access, models, error handling, testing |

## Quick example

```php
use FredBradley\TOPDesk\Facades\TOPDesk;

$incident = TOPDesk::createIncident([
    'callerLookup'     => ['id' => TOPDesk::getPersonByUsername('jsmith')->id],
    'briefDescription' => 'Laptop will not turn on',
    'entryType'        => ['name' => 'Phone'],
    'operatorGroup'    => ['id' => TOPDesk::getOperatorGroupId('I.T. Services')],
]);

echo $incident->number; // I-2024-00042
```
