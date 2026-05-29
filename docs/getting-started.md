# Getting Started

## Requirements

- PHP 8.2 or higher
- Laravel 9–13

## Installation

```bash
composer require fredbradley/topdesk
```

Laravel's package auto-discovery registers the service provider and `TOPDesk` facade alias automatically.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=topdesk.config
```

Add the following to your `.env`:

```env
TOPdesk_endpoint=https://yourcompany.topdesk.net/tas/
TOPdesk_app_username=api-user@yourcompany.com
TOPdesk_app_password=your-application-password
TOPdesk_ignore_cache=false
```

> **Note:** The endpoint must end with `tas/`. Application passwords are created in TOPdesk under _My Settings → Application passwords_.

If any of the three required values are missing or empty the package throws `ConfigNotFound` at boot time, so misconfiguration is caught early rather than at the first API call.

## Basic usage

All methods are available through the `TOPDesk` facade:

```php
use FredBradley\TOPDesk\Facades\TOPDesk;

// Fetch a person by their network login name
$person = TOPDesk::getPersonByUsername('jsmith');

// Create an incident
$incident = TOPDesk::createIncident([
    'callerLookup' => ['id' => $person->id],
    'briefDescription' => 'Printer not working',
    'entryType' => ['name' => 'Chat'],
]);

// Retrieve it back
$incident = TOPDesk::getIncident($incident->number);
```

## Raw HTTP access

For any endpoint not covered by a named method, use the HTTP primitives directly:

```php
$result = TOPDesk::get('api/incidents/call_types');

$created = TOPDesk::post('api/someEndpoint', ['key' => 'value']);
```

`get`, `post`, `put`, `patch`, and `delete` all return a decoded `object` (or an empty `array` for 204 No Content responses). Any 4xx/5xx response throws an `Illuminate\Http\Client\RequestException`.

If you need access to the raw `PendingRequest` to set custom headers or send multipart data:

```php
$response = TOPDesk::query()
    ->attach('file', file_get_contents($path), 'attachment.pdf')
    ->post('api/incidents/'.$incidentId.'/attachments');
```
