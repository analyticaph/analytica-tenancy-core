<?php

namespace Analytica\TenancyCore\Tests\Unit;

use Analytica\TenancyCore\Services\TenancyConfigService;
use Analytica\TenancyCore\Tests\TestCase;

class TenancyConfigServiceTest extends TestCase
{
    public function test_it_parses_domains_and_resolver_order(): void
    {
        $this->assertSame(
            ['localhost', 'auth.localhost'],
            TenancyConfigService::parseCentralDomains('localhost, auth.localhost')
        );

        $this->assertSame(
            ['host', 'header', 'path'],
            TenancyConfigService::parseResolverOrder('host, header, path')
        );
    }
}
