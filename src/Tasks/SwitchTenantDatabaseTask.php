<?php

namespace Analytica\TenancyCore\Tasks;

use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    protected ?array $originalConfig = null;

    public function __construct(
        protected string $connectionName = 'tenant'
    ) {
    }

    public function makeCurrent(IsTenant $tenant): void
    {
        $connectionKey = "database.connections.{$this->connectionName}";
        $currentConfig = (array) config($connectionKey, []);

        $this->originalConfig ??= $currentConfig;

        $nextConfig = array_merge($currentConfig, array_filter([
            'database' => $tenant->database ?? null,
            'host' => $tenant->db_host ?? null,
            'port' => $tenant->db_port ?? null,
            'username' => $tenant->db_username ?? null,
            'password' => $tenant->db_password ?? null,
            'driver' => $tenant->db_driver ?? null,
            'charset' => $tenant->db_charset ?? null,
            'collation' => $tenant->db_collation ?? null,
        ], static fn (mixed $value): bool => $value !== null));

        config()->set($connectionKey, $nextConfig);

        DB::purge($this->connectionName);
        DB::reconnect($this->connectionName);
    }

    public function forgetCurrent(): void
    {
        if ($this->originalConfig === null) {
            return;
        }

        config()->set("database.connections.{$this->connectionName}", $this->originalConfig);

        DB::purge($this->connectionName);
        DB::reconnect($this->connectionName);
    }
}
