<?php

namespace Analytica\TenancyCore\Resolvers;

use Analytica\TenancyCore\Contracts\ResolvesTenantKey;
use Analytica\TenancyCore\Services\TenancyConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostTenantKeyResolver implements ResolvesTenantKey
{
    public function name(): string
    {
        return 'host';
    }

    public function resolve(Request $request): ?string
    {
        $host = TenancyConfigService::normalizeHost($request->getHost());

        if ($host === '') {
            return null;
        }

        $centralDomains = array_map(
            [TenancyConfigService::class, 'normalizeHost'],
            config('tenancy-core.central_domains', [])
        );

        if (in_array($host, $centralDomains, true)) {
            return null;
        }

        foreach ((array) config('tenancy-core.resolver.base_domains', []) as $baseDomain) {
            $normalizedBaseDomain = TenancyConfigService::normalizeHost((string) $baseDomain);

            if ($normalizedBaseDomain === '' || ! Str::endsWith($host, '.'.$normalizedBaseDomain)) {
                continue;
            }

            $slug = Str::beforeLast($host, '.'.$normalizedBaseDomain);
            $slug = Str::before($slug, '.');

            return $slug !== '' ? $slug : null;
        }

        $segments = explode('.', $host);

        if (count($segments) >= 3) {
            $candidate = $segments[0];

            if (preg_match((string) config('tenancy-core.subdomain_regex'), $candidate) === 1) {
                return $candidate;
            }
        }

        return $host;
    }
}
