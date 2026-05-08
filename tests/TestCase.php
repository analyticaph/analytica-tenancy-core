<?php

namespace Analytica\TenancyCore\Tests;

use Analytica\TenancyCore\Models\Landlord\Tenant;
use Analytica\TenancyCore\TenancyCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Multitenancy\MultitenancyServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.env', 'testing');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.prefix', 'test');
        $app['config']->set('database.connections.tenant', [
            'driver' => 'pgsql',
            'database' => null,
            'host' => '127.0.0.1',
            'port' => 5432,
            'username' => 'postgres',
            'password' => '',
        ]);
        $app['config']->set('database.connections.landlord', [
            'driver' => 'pgsql',
            'database' => 'landlord',
            'host' => '127.0.0.1',
            'port' => 5432,
            'username' => 'postgres',
            'password' => '',
        ]);

        $app['config']->set('tenancy-core.landlord_connection', 'landlord');
        $app['config']->set('tenancy-core.tenant_connection', 'tenant');
        $app['config']->set('tenancy-core.central_domains', ['localhost', 'auth.localhost']);
        $app['config']->set('tenancy-core.resolver.order', ['host', 'header', 'path']);
        $app['config']->set('tenancy-core.resolver.header_name', 'X-Tenant');
        $app['config']->set('tenancy-core.resolver.path_prefix', '/t');
        $app['config']->set('tenancy-core.resolver.base_domains', ['portal.localhost', 'admin.localhost', 'lms.localhost']);
    }

    protected function getPackageProviders($app): array
    {
        return [
            MultitenancyServiceProvider::class,
            TenancyCoreServiceProvider::class,
        ];
    }

    protected function makeTenant(array $attributes = []): Tenant
    {
        $tenant = new Tenant();
        $tenant->forceFill(array_merge([
            'id' => 1,
            'name' => 'Northfield',
            'slug' => 'northfield',
            'status' => 'active',
            'database' => 'tenant_northfield',
        ], $attributes));
        $tenant->exists = true;

        return $tenant;
    }
}
