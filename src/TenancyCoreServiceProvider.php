<?php

namespace Analytica\TenancyCore;

use Analytica\TenancyCore\Console\InstallCommand;
use Analytica\TenancyCore\Http\Middleware\EnsureValidTenantUserMembership;
use Analytica\TenancyCore\Http\Middleware\NeedsTenantContext;
use Analytica\TenancyCore\Models\Landlord\Tenant;
use Analytica\TenancyCore\Support\CurrentTenant;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Spatie\Multitenancy\Contracts\IsTenant;

class TenancyCoreServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerBindings();
        $this->registerCommands();
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->registerMiddlewareAliases();
        $this->bootPublishing();
    }

    /**
     * Merge the package configuration with the host application config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/tenancy-core.php', 'tenancy-core');
        $this->mergeConfigFrom(__DIR__.'/config/multitenancy.php', 'multitenancy');
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(CurrentTenant::class, static fn (): CurrentTenant => new CurrentTenant());
        $this->app->bind(IsTenant::class, Tenant::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    protected function registerMiddlewareAliases(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('tenant.context', NeedsTenantContext::class);
        $router->aliasMiddleware('tenant.member', EnsureValidTenantUserMembership::class);
    }

    /**
     * Register publishable package resources.
     */
    protected function bootPublishing(): void
    {
        $this->publishes([
            __DIR__.'/config/tenancy-core.php' => config_path('tenancy-core.php'),
        ], 'tenancy-core-config');

        $this->publishes([
            __DIR__.'/config/multitenancy.php' => config_path('multitenancy.php'),
        ], 'tenancy-core-multitenancy-config');

        $this->publishes([
            dirname(__DIR__).'/database/migrations/landlord/2026_05_08_000001_create_tenants_table.php' => database_path('migrations/landlord/2026_05_08_000001_create_tenants_table.php'),
            dirname(__DIR__).'/database/migrations/landlord/2026_05_08_000002_create_tenant_domains_table.php' => database_path('migrations/landlord/2026_05_08_000002_create_tenant_domains_table.php'),
            dirname(__DIR__).'/database/migrations/landlord/2026_05_08_000003_create_tenant_user_memberships_table.php' => database_path('migrations/landlord/2026_05_08_000003_create_tenant_user_memberships_table.php'),
        ], 'tenancy-core-migrations');
    }
}
