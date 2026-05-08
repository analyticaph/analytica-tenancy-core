<?php

namespace Analytica\TenancyCore\Http\Middleware;

use Analytica\TenancyCore\Exceptions\MissingTenantException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NeedsTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $containerKey = config('multitenancy.current_tenant_container_key', 'currentTenant');

        if (app()->bound($containerKey) && app($containerKey) !== null) {
            return $next($request);
        }

        if ((bool) config('tenancy-core.resolver.fail_on_missing_tenant', true)) {
            throw MissingTenantException::forRequest();
        }

        abort(404, 'Tenant not found.');
    }
}
