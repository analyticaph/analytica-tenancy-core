<?php

namespace Analytica\TenancyCore\Resolvers;

use Analytica\TenancyCore\Contracts\ResolvesTenantKey;
use Illuminate\Http\Request;

class HeaderTenantKeyResolver implements ResolvesTenantKey
{
    public function name(): string
    {
        return 'header';
    }

    public function resolve(Request $request): ?string
    {
        $headerName = (string) config('tenancy-core.resolver.header_name', 'X-Tenant');

        $value = trim((string) $request->headers->get($headerName));

        return $value === '' ? null : $value;
    }
}
