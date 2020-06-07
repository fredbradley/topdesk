# TOPdesk API Service Provider

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require fredbradley/topdesk
```

## Set Up
Ensure you understand the TOPdesk API configuration and your TOPdesk environment is set up to use the API. More details can be found at [developers.topdesk.com](https://developers.topdesk.com/tutorial.html#:~:text=To%20create%20an%20Application%20password,in%20the%20Application%20passwords%20block.&text=In%20addition%20to%20a%20name,be%20set%20for%20the%20password.).

Add three variables to your `.env` file
``` txt
TOPdesk_endpoint="https://servicedesk.cranleigh.org/tas" # Your TOPdesk url, ending in "`tas/`"
TOPdesk_app_username="frb" # Your username you wish to authenticate with
TOPdesk_app_password="52h63-2b7aw-tctkb-2lz2k-jejah" # Your application password for that username. 
```


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

[ico-version]: https://img.shields.io/packagist/v/fredbradley/topdesk.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/fredbradley/topdesk.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/fredbradley/topdesk/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/fredbradley/topdesk
[link-downloads]: https://packagist.org/packages/fredbradley/topdesk
[link-travis]: https://travis-ci.org/fredbradley/topdesk
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/fredbradley
[link-contributors]: ../../contributors
