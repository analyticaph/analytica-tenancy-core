<?php

use Analytica\TenancyCore\Services\TenancyConfigService;

return [
    'landlord_connection' => env('TENANCY_CORE_LANDLORD_CONNECTION', 'landlord'),

    'tenant_connection' => env('TENANCY_CORE_TENANT_CONNECTION', 'tenant'),

    'tenant_database_prefix' => env('TENANCY_CORE_TENANT_DATABASE_PREFIX', 'tenant_'),

    'subdomain_regex' => env(
        'TENANCY_CORE_SUBDOMAIN_REGEX',
        '/^(?<tenant>[a-z0-9]+(?:-[a-z0-9]+)*)$/'
    ),

    'central_domains' => TenancyConfigService::parseCentralDomains(
        (string) env('TENANCY_CORE_CENTRAL_DOMAINS', 'localhost,127.0.0.1')
    ),

    'resolver' => [
        'order' => TenancyConfigService::parseResolverOrder(
            (string) env('TENANCY_CORE_RESOLVER_ORDER', 'host,header,path')
        ),
        'header_name' => env('TENANCY_CORE_HEADER_NAME', 'X-Tenant'),
        'path_prefix' => TenancyConfigService::normalizePathPrefix(
            (string) env('TENANCY_CORE_PATH_PREFIX', '/t')
        ),
        'central_domains' => TenancyConfigService::parseCentralDomains(
            (string) env('TENANCY_CORE_CENTRAL_DOMAINS', 'localhost,127.0.0.1')
        ),
        'base_domains' => TenancyConfigService::parseDomainList(
            (string) env('TENANCY_CORE_BASE_DOMAINS', 'portal.localhost,admin.localhost,lms.localhost')
        ),
        'fail_on_missing_tenant' => (bool) env('TENANCY_CORE_FAIL_ON_MISSING_TENANT', true),
    ],

    'cache_prefixing' => [
        'enabled' => (bool) env('TENANCY_CORE_CACHE_PREFIXING_ENABLED', true),
    ],

    'permission_cache_reset' => [
        'enabled' => (bool) env('TENANCY_CORE_PERMISSION_CACHE_RESET_ENABLED', true),
    ],
];
