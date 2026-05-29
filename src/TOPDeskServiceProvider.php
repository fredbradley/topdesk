<?php

declare(strict_types=1);

namespace FredBradley\TOPDesk;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;

class TOPDeskServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /*
         * Pattern: Http::macro() extends Laravel's HTTP client with a named,
         * pre-configured PendingRequest. All credential and base-URL knowledge
         * lives here — nothing else in the package needs to know how auth works.
         *
         * The closure is bound to the PendingRequest instance at call time,
         * so $this refers to the PendingRequest being built, not the service provider.
         * This is why withBasicAuth() and baseUrl() are called on $this rather
         * than as static calls on PendingRequest.
         */
        PendingRequest::macro('topdeskAuth', function (): PendingRequest {
            return $this->acceptJson()
                ->withBasicAuth(
                    config('topdesk.application_username'),
                    config('topdesk.application_password')
                )
                ->baseUrl(rtrim(config('topdesk.endpoint'), '/\\').'/');
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/topdesk.php' => config_path('topdesk.php'),
            ], 'topdesk.config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/topdesk.php', 'topdesk');

        /*
         * Pattern: singleton registration binds the class to the container
         * under a string key. The Facade's getFacadeAccessor() resolves it
         * by that key, so neither the Facade nor callers need to import TOPDesk.
         */
        $this->app->singleton('topdesk', fn () => new TOPDesk);
    }

    public function provides(): array
    {
        return ['topdesk'];
    }
}
