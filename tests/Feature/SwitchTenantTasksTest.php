<?php

namespace Analytica\TenancyCore\Tests\Feature;

use Analytica\TenancyCore\Tasks\PrefixCacheTask;
use Analytica\TenancyCore\Tasks\SwitchTenantDatabaseTask;
use Analytica\TenancyCore\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class SwitchTenantTasksTest extends TestCase
{
    public function test_switch_tenant_database_task_rewrites_the_connection_config(): void
    {
        $tenant = $this->makeTenant([
            'db_host' => '127.0.0.1',
            'db_port' => 3307,
            'db_username' => 'tenant_user',
            'db_password' => 'tenant_pass',
        ]);

        DB::shouldReceive('purge')->twice()->with('tenant');
        DB::shouldReceive('reconnect')->twice()->with('tenant');

        $task = new SwitchTenantDatabaseTask('tenant');
        $task->makeCurrent($tenant);

        $this->assertSame('tenant_northfield', config('database.connections.tenant.database'));
        $this->assertSame('127.0.0.1', config('database.connections.tenant.host'));
        $this->assertSame(3307, config('database.connections.tenant.port'));
        $this->assertSame('tenant_user', config('database.connections.tenant.username'));

        $task->forgetCurrent();
    }

    public function test_prefix_cache_task_isolates_the_cache_prefix(): void
    {
        $tenant = $this->makeTenant();

        $task = new PrefixCacheTask(true);
        $task->makeCurrent($tenant);

        $this->assertSame("tenant_{$tenant->id}", config('cache.prefix'));

        $task->forgetCurrent();

        $this->assertSame('test', config('cache.prefix'));
    }
}
