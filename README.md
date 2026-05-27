# TOPdesk API Service Provider

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fredbradley/topdesk.svg?style=flat-square)](https://packagist.org/packages/fredbradley/topdesk)
[![Total Downloads](https://img.shields.io/packagist/dt/fredbradley/topdesk.svg?style=flat-square)](https://packagist.org/packages/fredbradley/topdesk)
![StyleCI Status](https://github.styleci.io/repos/270444651/shield)

This is a TOPdesk API wrapper for Laravel. Using Laravel's HTTP Facade. Please check out the [Version 1 releases](https://github.com/fredbradley/topdesk/tree/v1.2.15) if you do not use Laravel, or the original package from [Innovaat](https://github.com/innovaat/topdesk-php).  

## Minimum Requirements
- PHP 8.2 or higher
- Laravel 9–13

## Installation

Via Composer

``` bash
$ composer require fredbradley/topdesk
```
## Set up
Ensure you understand the TOPdesk API configuration and your TOPdesk environment is set up to use the API. More details can be found at [developers.topdesk.com](https://developers.topdesk.com/tutorial.html#:~:text=To%20create%20an%20Application%20password,in%20the%20Application%20passwords%20block.&text=In%20addition%20to%20a%20name,be%20set%20for%20the%20password.).

Add three variables to your `.env` file
``` txt
TOPdesk_endpoint="" # Your TOPdesk url, ending in "`tas/`"
TOPdesk_app_username="" # Your username you wish to authenticate with
TOPdesk_app_password="" # Your application password for that username. 
```


## Guide
Our TOPdesk API implementation contains the following features:
- Simple login using application passwords.
- Response caching (5-minute default TTL) to reduce API load; cache can be busted per-call or disabled globally.
- Direct function calls for common API endpoints (`createIncident($params)`, `getIncident($number)`,
`getListOfIncidents()`, `escalateIncident($id, $reasonId)`, `deescalateIncident($id, $reasonId)`, `getDepartments()`,
`createDepartment($name)`, `getBranches()`, `createBranch($data)` among others).
- Low-level HTTP helpers (`get()`, `post()`, `put()`, `patch()`, `delete()`) for endpoints not covered by named methods.


Now your API should be ready to use:
```php
$incidents = TOPDesk::getListOfIncidents([
    'start' => 0,
    'page_size' => 10
]);

foreach($incidents as $incident) {
    var_dump($incident['number']);
}
```

Many requests have been implemented as direct functions of the API. However, not all of them have been implemented.
For unlisted endpoints you can call the HTTP helpers directly:
```php
// GET with optional query parameters
TOPDesk::get('api/incidents/call_types', [
    // Optional key => value query parameters
]);

// POST / PUT / PATCH with a JSON body
TOPDesk::post('api/incidents', [
    'callerLookup' => ['id' => $callerId],
    'briefDescription' => 'My incident',
]);
```

## Documentation
- https://developers.topdesk.com/

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Fred Bradley](https://www.fredbradley.uk) - Laravel Wrapper
- [Innovaat](https://github.com/innovaat/topdesk-php) - Initial TOPdesk API PHP Wrapper

## License

MIT. Please see the [license file](LICENSE.md) for more information.

