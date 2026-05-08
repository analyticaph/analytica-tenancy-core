<?php

namespace Analytica\TenancyCore\Tests\Feature;

use Analytica\TenancyCore\Models\Landlord\Tenant;
use Analytica\TenancyCore\Tests\TestCase;

class TenantModelTest extends TestCase
{
    public function test_landlord_tenant_model_uses_the_landlord_connection_name(): void
    {
        $tenant = new Tenant();

        $this->assertSame('landlord', $tenant->getConnectionName());
    }
}
