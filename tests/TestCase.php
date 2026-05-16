<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk\Tests;

use FredBradley\TOPDesk\TOPDeskServiceProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        // Throw if any test accidentally makes a real HTTP call without a fake.
        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [TOPDeskServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('topdesk', [
            'endpoint'             => 'https://company.topdesk.net/tas/',
            'application_username' => 'test@example.com',
            'application_password' => 'test-password',
            'ignore_cache'         => false,
        ]);

        // Use the array driver so cache is isolated per test.
        $app['config']->set('cache.default', 'array');
    }
}
