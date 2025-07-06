<?php

namespace Osamahdev1\LaravelGcpIntegration;

use Illuminate\Support\ServiceProvider;
use Osamahdev1\LaravelGcpIntegration\Console\Commands\MakeIntegration;

class LaravelGcpIntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the dynamic integration manager
        $this->app->singleton('laravel-gcp-integration.manager', function ($app) {
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
            __DIR__ . '/../config/gcp-integration.php' => config_path('gcp-integration.php'),
        ], 'config');
    }
}
