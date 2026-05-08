<?php

namespace Analytica\TenancyCore\Resolvers;

use Analytica\TenancyCore\Contracts\ResolvesTenantKey;
use Analytica\TenancyCore\Services\TenancyConfigService;
use Illuminate\Http\Request;

class PathTenantKeyResolver implements ResolvesTenantKey
{
    public function name(): string
    {
        return 'path';
    }

    public function resolve(Request $request): ?string
    {
        if (! app()->environment(['local', 'testing'])) {
            return null;
        }

        $prefix = trim(TenancyConfigService::normalizePathPrefix(
            (string) config('tenancy-core.resolver.path_prefix', '/t')
        ), '/');

        $segments = $request->segments();

        if (($segments[0] ?? null) !== $prefix) {
            return null;
        }

        $tenantKey = trim((string) ($segments[1] ?? ''));

        return $tenantKey === '' ? null : $tenantKey;
    }
}
