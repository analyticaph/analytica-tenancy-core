<?php

namespace Analytica\TenancyCore;

use Illuminate\Support\ServiceProvider;

class TenancyCoreServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->bootPublishing();
    }

    /**
     * Merge the package configuration with the host application config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/tenancy-core.php', 'tenancy-core');
    }

    /**
     * Register publishable package resources.
     */
    protected function bootPublishing(): void
    {
        $this->publishes([
            __DIR__.'/config/tenancy-core.php' => config_path('tenancy-core.php'),
        ], 'tenancy-core-config');
    }
}
