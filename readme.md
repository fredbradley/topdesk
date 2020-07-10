# TOPdesk API Service Provider

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fredbradley/cacher.svg?style=flat-square)](https://packagist.org/packages/fredbradley/cacher)
[![Total Downloads](https://img.shields.io/packagist/dt/fredbradley/cacher.svg?style=flat-square)](https://packagist.org/packages/fredbradley/xirrusapi)
![StyleCI Status](https://github.styleci.io/repos/270444651/shield)


Primarily a Service Provider for Laravel, but this package can be used on any PHP script. It connects with a TOPdesk instance with API permissions. It uses [Innovaat's](https://github.com/innovaat/topdesk-php) package as a basis.  

## Early Adoptors Beware
This is intended as a wrapper for the Laravel Ecosystem for a 3rd party product TOPdesk. As such in version 2 we shall be rapidly changing the responses to deliver a resource response more native to Laravel, rather than the flat Guzzle output.

## Minimum Requirements
- PHP 7.2 or higher

## Installation

Via Composer

``` bash
$ composer require fredbradley/topdesk
```

## Set Up
Ensure you understand the TOPdesk API configuration and your TOPdesk environment is set up to use the API. More details can be found at [developers.topdesk.com](https://developers.topdesk.com/tutorial.html#:~:text=To%20create%20an%20Application%20password,in%20the%20Application%20passwords%20block.&text=In%20addition%20to%20a%20name,be%20set%20for%20the%20password.).

Add three variables to your `.env` file
``` txt
TOPdesk_endpoint="" # Your TOPdesk url, ending in "`tas/`"
TOPdesk_app_username="" # Your username you wish to authenticate with
TOPdesk_app_password="" # Your application password for that username. 
```


## Guide
Our TOPdesk API implementation contains the following features:
- Simple login using application passwords (recommended) or tokens (legacy).
- Automatic retry functionality that retries requests when connection errors or status codes >= 500 occur.
 We have experienced various instabilities with the TOPdesk API, and hopefully this minimizes these shortcomings. 
- Direct function calls for much used api endpoints (`createIncident($params)`, `getIncidentById($id)`,
`getListOfIncidents()`, `escalateIncidentById($id)`, `deescalateIncidentById($id)`, `getListOfDepartments()`,
`createDepartment($params)`, `getListOfBranches()`, `createBranch($params)` among others).
- Easy syntax for all other endpoints using `$api->request($method, $uri, $json = [], $query = [])`.


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
For manual API requests, use the `request()` function:
```php
TOPDesk::request('GET', 'api/incidents/call_types', [
    // Optional array to be sent as JSON body (for POST/PUT requests).
], [
    // Optional (search) query parameters, see API documentation for supported values.
], [
    // Optional parameters for the Guzzle request itself.
    // @see http://docs.guzzlephp.org/en/stable/request-options.html
])
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

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Fred Bradley](https://www.fredbradley.uk) - Laravel Wrapper
- [Innovaat](https://github.com/innovaat/topdesk-php) - Initial TOPdesk API PHP Wrapper

## License

license. Please see the [license file](license.md) for more information.

