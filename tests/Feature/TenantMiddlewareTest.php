<?php

namespace Analytica\TenancyCore\Tests\Feature;

use Analytica\TenancyCore\Exceptions\InvalidTenantUserMembershipException;
use Analytica\TenancyCore\Exceptions\MissingTenantException;
use Analytica\TenancyCore\Http\Middleware\EnsureValidTenantUserMembership;
use Analytica\TenancyCore\Http\Middleware\NeedsTenantContext;
use Analytica\TenancyCore\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddlewareTest extends TestCase
{
    public function test_tenant_context_middleware_fails_when_tenant_is_missing(): void
    {
        $middleware = app(NeedsTenantContext::class);

        $this->expectException(MissingTenantException::class);

        $middleware->handle(Request::create('/dashboard'), fn (): Response => new Response());
    }

    public function test_tenant_member_middleware_blocks_users_without_membership(): void
    {
        $tenant = $this->makeTenant();

        app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);

        $request = Request::create('/dashboard');
        $request->setUserResolver(fn () => $this->makeUser(99));

        $this->expectException(InvalidTenantUserMembershipException::class);

        $middleware = new class extends EnsureValidTenantUserMembership
        {
            protected function hasValidMembership(int|string $tenantId, int|string $userId): bool
            {
                return false;
            }
        };

        $middleware->handle(
            $request,
            fn (): Response => new Response()
        );
    }

    public function test_tenant_member_middleware_allows_valid_membership(): void
    {
        $tenant = $this->makeTenant();
        $user = $this->makeUser(10);

        app()->instance(config('multitenancy.current_tenant_container_key', 'currentTenant'), $tenant);

        $request = Request::create('/dashboard');
        $request->setUserResolver(fn () => $user);

        $middleware = new class extends EnsureValidTenantUserMembership
        {
            protected function hasValidMembership(int|string $tenantId, int|string $userId): bool
            {
                return true;
            }
        };

        $response = $middleware->handle(
            $request,
            fn (): Response => new Response('ok')
        );

        $this->assertSame('ok', $response->getContent());
    }

    protected function makeUser(int $id): object
    {
        $user = Mockery::mock(\Illuminate\Contracts\Auth\Authenticatable::class);
        $user->shouldReceive('getAuthIdentifier')->andReturn($id);

        return $user;
    }
}
