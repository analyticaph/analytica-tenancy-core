<?php

namespace Analytica\TenancyCore\Http\Middleware;

use Analytica\TenancyCore\Exceptions\InvalidTenantUserMembershipException;
use Analytica\TenancyCore\Models\Landlord\TenantUserMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidTenantUserMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $containerKey = config('multitenancy.current_tenant_container_key', 'currentTenant');
        $tenant = app()->bound($containerKey) ? app($containerKey) : null;

        if ($user === null || $tenant === null) {
            return $next($request);
        }

        $membershipExists = $this->hasValidMembership(
            $tenant->getKey(),
            $user->getAuthIdentifier()
        );

        if (! $membershipExists) {
            throw InvalidTenantUserMembershipException::forUser(
                $user->getAuthIdentifier(),
                $tenant->getKey()
            );
        }

        return $next($request);
    }

    protected function hasValidMembership(int|string $tenantId, int|string $userId): bool
    {
        return TenantUserMembership::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where(function ($query): void {
                $query->whereNull('status')->orWhere('status', 'active');
            })
            ->exists();
    }
}
