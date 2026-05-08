<?php

namespace Analytica\TenancyCore\TenantFinder;

use Analytica\TenancyCore\Contracts\ResolvesTenantKey;
use Analytica\TenancyCore\Exceptions\InactiveTenantException;
use Analytica\TenancyCore\Models\Landlord\Tenant;
use Analytica\TenancyCore\Resolvers\HeaderTenantKeyResolver;
use Analytica\TenancyCore\Resolvers\HostTenantKeyResolver;
use Analytica\TenancyCore\Resolvers\PathTenantKeyResolver;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SmartCampusTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        foreach ($this->resolvers() as $resolver) {
            $identifier = $resolver->resolve($request);

            if ($identifier === null) {
                continue;
            }

            $tenant = $this->findTenantByIdentifier($identifier);

            if ($tenant === null) {
                continue;
            }

            if (! $tenant->isActive()) {
                throw InactiveTenantException::forIdentifier($identifier);
            }

            return $tenant;
        }

        return null;
    }

    /**
     * @return array<int, ResolvesTenantKey>
     */
    protected function resolvers(): array
    {
        $resolverMap = [
            'host' => HostTenantKeyResolver::class,
            'header' => HeaderTenantKeyResolver::class,
            'path' => PathTenantKeyResolver::class,
        ];

        $resolvers = [];

        foreach ((array) config('tenancy-core.resolver.order', ['host', 'header', 'path']) as $resolverName) {
            $resolverClass = $resolverMap[$resolverName] ?? null;

            if ($resolverClass === null) {
                continue;
            }

            $resolvers[] = app($resolverClass);
        }

        return $resolvers;
    }

    protected function findTenantByIdentifier(string $identifier): ?Tenant
    {
        return Tenant::query()
            ->where('slug', $identifier)
            ->orWhere('database', $identifier)
            ->orWhereHas('domains', function ($query) use ($identifier): void {
                $query->where('domain', $identifier);
            })
            ->first();
    }
}
