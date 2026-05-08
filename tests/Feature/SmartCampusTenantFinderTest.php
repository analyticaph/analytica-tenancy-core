<?php

namespace Analytica\TenancyCore\Tests\Feature;

use Analytica\TenancyCore\Exceptions\InactiveTenantException;
use Analytica\TenancyCore\Models\Landlord\Tenant;
use Analytica\TenancyCore\TenantFinder\SmartCampusTenantFinder;
use Analytica\TenancyCore\Tests\TestCase;
use Illuminate\Http\Request;

class SmartCampusTenantFinderTest extends TestCase
{
    public function test_it_resolves_an_active_tenant_from_the_full_host(): void
    {
        $tenant = $this->makeFinder()->findForRequest(
            Request::create('http://northfield.portal.localhost/dashboard')
        );

        $this->assertSame('northfield', $tenant?->slug);
    }

    public function test_it_resolves_an_active_tenant_from_the_subdomain(): void
    {
        $tenant = $this->makeFinder()->findForRequest(
            Request::create('http://northfield.admin.localhost/dashboard')
        );

        $this->assertSame('northfield', $tenant?->slug);
    }

    public function test_it_resolves_an_active_tenant_from_the_header(): void
    {
        $request = Request::create('http://auth.localhost/introspect');
        $request->headers->set('X-Tenant', 'northfield');

        $tenant = $this->makeFinder()->findForRequest($request);

        $this->assertSame('northfield', $tenant?->slug);
    }

    public function test_it_resolves_an_active_tenant_from_the_local_path_fallback(): void
    {
        $tenant = $this->makeFinder()->findForRequest(
            Request::create('http://localhost/t/northfield/apply')
        );

        $this->assertSame('northfield', $tenant?->slug);
    }

    public function test_it_skips_central_domains_without_other_context(): void
    {
        $tenant = $this->makeFinder()->findForRequest(
            Request::create('http://auth.localhost/login')
        );

        $this->assertNull($tenant);
    }

    public function test_it_rejects_inactive_tenants(): void
    {
        $this->expectException(InactiveTenantException::class);

        $request = Request::create(
            'http://auth.localhost',
            'GET',
            [],
            [],
            [],
            ['HTTP_X_TENANT' => 'inactive-school']
        );

        $this->makeFinder([
            'inactive-school' => $this->makeTenant([
                'id' => 2,
                'slug' => 'inactive-school',
                'status' => 'inactive',
                'database' => 'tenant_inactive',
            ]),
        ])->findForRequest($request);
    }

    protected function makeFinder(array $tenants = []): SmartCampusTenantFinder
    {
        $tenants = array_merge([
            'northfield' => $this->makeTenant(),
            'northfield.portal.localhost' => $this->makeTenant(),
        ], $tenants);

        return new class($tenants) extends SmartCampusTenantFinder
        {
            public function __construct(
                protected array $tenants
            ) {
            }

            protected function findTenantByIdentifier(string $identifier): ?Tenant
            {
                return $this->tenants[$identifier] ?? null;
            }
        };
    }
}
