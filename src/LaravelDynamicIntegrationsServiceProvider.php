<?php

namespace Osamah\LaravelDynamicIntegrations;

use Illuminate\Support\ServiceProvider;
use Osamah\LaravelDynamicIntegrations\Console\Commands\MakeIntegration;

class LaravelDynamicIntegrationsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the dynamic integration manager
        $this->app->singleton('laravel-dynamic-integrations.manager', function ($app) {
            return new class {
                // This is just a placeholder - the real magic happens in the facade
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeIntegration::class,
            ]);
        }

        // Publish configuration if needed
        $this->publishes([
            __DIR__ . '/../config/dynamic-integrations.php' => config_path('dynamic-integrations.php'),
        ], 'config');
    }
}
