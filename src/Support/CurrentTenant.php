<?php

namespace Analytica\TenancyCore\Support;

use Spatie\Multitenancy\Contracts\IsTenant;
use Analytica\TenancyCore\Models\Landlord\Tenant;

class CurrentTenant
{
    public function tenant(): ?Tenant
    {
        $containerKey = config('multitenancy.current_tenant_container_key', 'currentTenant');
        $tenant = app()->bound($containerKey) ? app($containerKey) : null;

        return $tenant instanceof Tenant ? $tenant : null;
    }

    public function raw(): IsTenant|null
    {
        $containerKey = config('multitenancy.current_tenant_container_key', 'currentTenant');
        $tenant = app()->bound($containerKey) ? app($containerKey) : null;

        return $tenant instanceof IsTenant ? $tenant : null;
    }

    public function id(): int|string|null
    {
        return $this->tenant()?->getKey();
    }

    public function slug(): ?string
    {
        return $this->tenant()?->slug;
    }

    public function host(): ?string
    {
        return request()?->getHost();
    }

    public function landlordConnection(): string
    {
        return (string) config('tenancy-core.landlord_connection', 'landlord');
    }

    public function tenantConnection(): string
    {
        return (string) config('tenancy-core.tenant_connection', 'tenant');
    }
}
