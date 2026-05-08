<?php

namespace Analytica\TenancyCore\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class ResetPermissionCacheTask implements SwitchTenantTask
{
    public function __construct(
        protected bool $enabled = true
    ) {
    }

    public function makeCurrent(IsTenant $tenant): void
    {
        $this->forgetPermissions();
    }

    public function forgetCurrent(): void
    {
        $this->forgetPermissions();
    }

    protected function forgetPermissions(): void
    {
        if (! $this->enabled || ! class_exists(\Spatie\Permission\PermissionRegistrar::class)) {
            return;
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
