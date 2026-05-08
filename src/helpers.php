<?php

use Analytica\TenancyCore\Support\CurrentTenant;

if (! function_exists('currentTenantId')) {
    function currentTenantId(): int|string|null
    {
        return app(CurrentTenant::class)->id();
    }
}

if (! function_exists('currentTenantSlug')) {
    function currentTenantSlug(): ?string
    {
        return app(CurrentTenant::class)->slug();
    }
}
