<?php

use Analytica\TenancyCore\Services\TenancyConfigService;

return [
    'landlord_connection' => env('TENANCY_CORE_LANDLORD_CONNECTION', 'landlord'),

    'tenant_database_prefix' => env('TENANCY_CORE_TENANT_DATABASE_PREFIX', 'tenant_'),

    'subdomain_regex' => env(
        'TENANCY_CORE_SUBDOMAIN_REGEX',
        '/^(?<tenant>[a-z0-9]+(?:-[a-z0-9]+)*)$/'
    ),

    'central_domains' => TenancyConfigService::parseCentralDomains(
        (string) env('TENANCY_CORE_CENTRAL_DOMAINS', 'localhost,127.0.0.1')
    ),
];
