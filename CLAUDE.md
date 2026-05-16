# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About this project

`fredbradley/topdesk` is a Laravel package that wraps the TOPdesk REST API. It supports Laravel 9–13 and PHP ^8.2.

## Commands

```bash
# Run tests
composer test
# or directly
./vendor/bin/phpunit

# Format code (Laravel Pint)
./vendor/bin/pint

# Run a single test file
./vendor/bin/phpunit tests/Path/To/TestFile.php

# Run a single test method
./vendor/bin/phpunit --filter testMethodName
```

Code style is enforced by StyleCI (preset: laravel). Run Pint locally before committing.

## Architecture

### Entry points

- `src/TOPDesk.php` — main class; composes all domain traits
- `src/Facades/TOPDesk.php` — static facade alias
- `src/TOPDeskServiceProvider.php` — registers the singleton, publishes config, and adds `Http::topdeskAuth()` macro for pre-authenticated requests

### Trait-based domain split

Functionality is organised into traits mixed into `TOPDesk`:

| Trait | Domain |
|---|---|
| `Incidents` | Create, update, list incidents/tickets |
| `Persons` | Person/user lookups |
| `Assets` | Asset management |
| `Changes` | Change management |
| `Counts` | Statistical counts |
| `OperatorStats` | Operator-level statistics |
| `DeprecatedMethods` | Stubs that throw on use |

### Models

`src/Models/BaseModel.php` is an abstract base for TOPdesk entities (`Operator`, `Person`, `OperatorGroup`, `PersonGroup`, `Asset`). It provides:

- `find($variable, $value)` / `findById($id)` / `findFirstByVariable($variable, $value)` — query the API with 5-minute caching
- `whereVariableEquals($field, $value)` — filtered collection lookup
- Properties are assigned dynamically from API JSON responses

### HTTP layer

`TOPDesk` wraps Laravel's `Http` facade with basic auth (configured via `TOPdesk_app_username` / `TOPdesk_app_password`). The private helpers `get()`, `post()`, `put()`, `patch()`, `delete()` all return parsed objects; 204 No Content returns an empty array.

### Caching

Uses `fredbradley/cacher`. Default TTL is 5 minutes (`EasySeconds::minutes(5)`). Cache keys are slugified endpoint + query variable names. Most public methods accept a `bool $forgetCache` parameter to bypass or clear the cache. Set `TOPdesk_ignore_cache=true` in `.env` to disable globally.

### Configuration

Published via `php artisan vendor:publish`. Environment variables:

```
TOPdesk_endpoint=
TOPdesk_app_username=
TOPdesk_app_password=
TOPdesk_ignore_cache=false
```

`ConfigNotFound` is thrown during boot if any of the first three are missing.

### Exceptions

- `ConfigNotFound` — missing or empty config value
- `OperatorNotFound` — operator lookup returned nothing (HTTP 404)
- `OperatorGroupNotFound` — operator group lookup returned nothing

### FIQL queries

The TOPdesk API uses FIQL syntax for filtering. Pass query strings like `(field==value);(other!=value)` in the `query` key of request params.