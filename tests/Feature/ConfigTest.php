<?php

declare(strict_types=1);

use FredBradley\TOPDesk\Exceptions\ConfigNotFound;

it('boots successfully when all required config values are present', function () {
    // The singleton is resolved here; if checkConfig() throws the test fails.
    expect(fn () => app('topdesk'))->not->toThrow(ConfigNotFound::class);
});

it('throws ConfigNotFound when the endpoint is null', function () {
    config(['topdesk.endpoint' => null]);
    $this->app->forgetInstance('topdesk');

    expect(fn () => $this->app->make('topdesk'))->toThrow(ConfigNotFound::class);
});

it('throws ConfigNotFound when the endpoint is an empty string', function () {
    config(['topdesk.endpoint' => '']);
    $this->app->forgetInstance('topdesk');

    expect(fn () => $this->app->make('topdesk'))->toThrow(ConfigNotFound::class);
});

it('throws ConfigNotFound when the username is null', function () {
    config(['topdesk.application_username' => null]);
    $this->app->forgetInstance('topdesk');

    expect(fn () => $this->app->make('topdesk'))->toThrow(ConfigNotFound::class);
});

it('throws ConfigNotFound when the password is null', function () {
    config(['topdesk.application_password' => null]);
    $this->app->forgetInstance('topdesk');

    expect(fn () => $this->app->make('topdesk'))->toThrow(ConfigNotFound::class);
});

it('includes the offending config key in the exception message', function () {
    config(['topdesk.endpoint' => null]);
    $this->app->forgetInstance('topdesk');

    try {
        $this->app->make('topdesk');
        fail('Expected ConfigNotFound');
    } catch (ConfigNotFound $e) {
        expect($e->getMessage())->toContain('topdesk.endpoint');
    }
});
