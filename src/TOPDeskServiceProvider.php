<?php

namespace FredBradley\TOPDesk;

use Illuminate\Support\ServiceProvider;

class TOPDeskServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'fredbradley');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'fredbradley');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/topdesk.php', 'topdesk');

        // Register the service the package provides.
        $this->app->singleton('topdesk', function ($app) {
            return new TOPDesk;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['topdesk'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/topdesk.php' => config_path('topdesk.php'),
        ], 'topdesk.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/fredbradley'),
        ], 'topdesk.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/fredbradley'),
        ], 'topdesk.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/fredbradley'),
        ], 'topdesk.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
