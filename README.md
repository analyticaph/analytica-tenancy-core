# Analytica Tenancy Core

Shared Laravel tenancy package for the Analytica ecosystem. This package standardizes tenant discovery, landlord models, middleware, and tenant database switching for Smart Campus applications using `spatie/laravel-multitenancy` with the multiple-database approach.

## Features

- Custom Smart Campus tenant finder
- Publishable `tenancy-core` and `multitenancy` config
- Landlord `Tenant`, `TenantDomain`, and `TenantUserMembership` models
- Tenant-required and tenant-membership middleware aliases
- Custom tenant DB switching, cache prefixing, and permission cache reset tasks
- Publishable landlord migrations
- Install command for first-time setup

## Requirements

- PHP `^8.2`
- Laravel `^12.0`
- `spatie/laravel-multitenancy` `^4.0`

## Installation

### 1. Add the private repository

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:analyticaph/analytica-tenancy-core.git"
    }
  ]
}
```

### 2. Require the package

```bash
composer require analytica/tenancy-core
```

### 3. Publish package assets

```bash
php artisan tenancy-core:install
```

This publishes:

- `config/tenancy-core.php`
- `config/multitenancy.php`
- landlord migrations into `database/migrations/landlord`

## Configuration

Important environment values:

```env
TENANCY_CORE_LANDLORD_CONNECTION=landlord
TENANCY_CORE_TENANT_CONNECTION=tenant
TENANCY_CORE_TENANT_DATABASE_PREFIX=tenant_
TENANCY_CORE_SUBDOMAIN_REGEX=/^(?<tenant>[a-z0-9]+(?:-[a-z0-9]+)*)$/
TENANCY_CORE_CENTRAL_DOMAINS=localhost,127.0.0.1,auth.localhost
TENANCY_CORE_BASE_DOMAINS=portal.localhost,admin.localhost,lms.localhost
TENANCY_CORE_RESOLVER_ORDER=host,header,path
TENANCY_CORE_HEADER_NAME=X-Tenant
TENANCY_CORE_PATH_PREFIX=/t
TENANCY_CORE_FAIL_ON_MISSING_TENANT=true
TENANCY_CORE_CACHE_PREFIXING_ENABLED=true
TENANCY_CORE_PERMISSION_CACHE_RESET_ENABLED=true
```

Stable config keys exposed by the package:

- `tenancy-core.landlord_connection`
- `tenancy-core.tenant_connection`
- `tenancy-core.resolver.order`
- `tenancy-core.resolver.header_name`
- `tenancy-core.resolver.path_prefix`
- `tenancy-core.central_domains`

## Host App Requirements

Every tenant-aware business app must:

- add `landlord` and `tenant` connections in `config/database.php`
- set the `tenant` connection `database` value to `null`
- apply `tenant.context` to tenant business routes
- apply `tenant.member` to authenticated tenant routes that require membership validation
- keep OAuth, login, callback, and global lookup routes outside tenant-required middleware where appropriate
- mark landlord models with `UsesLandlordConnection`
- mark tenant business models with `UsesTenantConnection`

Example database setup:

```php
'connections' => [
    'tenant' => [
        'driver' => 'mysql',
        'database' => null,
        'host' => env('TENANT_DB_HOST', env('DB_HOST')),
        'port' => env('TENANT_DB_PORT', env('DB_PORT')),
        'username' => env('TENANT_DB_USERNAME', env('DB_USERNAME')),
        'password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD')),
    ],

    'landlord' => [
        'driver' => 'mysql',
        'database' => env('LANDLORD_DB_DATABASE'),
        'host' => env('LANDLORD_DB_HOST', env('DB_HOST')),
        'port' => env('LANDLORD_DB_PORT', env('DB_PORT')),
        'username' => env('LANDLORD_DB_USERNAME', env('DB_USERNAME')),
        'password' => env('LANDLORD_DB_PASSWORD', env('DB_PASSWORD')),
    ],
],
```

## Tenant Resolution

The package resolves tenants in this order by default:

1. Host or subdomain
2. `X-Tenant` request header
3. Local path fallback like `/t/northfield/...` in `local` and `testing`

Use cases:

- browser traffic: `northfield.portal.localhost`
- internal requests: `X-Tenant: northfield`
- local browser fallback: `http://localhost:8002/t/northfield/apply`

Configured central domains are never treated as tenants.

## Middleware Aliases

- `tenant.context`: require a resolved current tenant
- `tenant.member`: require the authenticated user to belong to the current tenant

## Helpers

- `app('currentTenant')`
- `currentTenantId()`
- `currentTenantSlug()`
- `app(\Analytica\TenancyCore\Support\CurrentTenant::class)`

## Migrations

Published landlord migrations create:

- `tenants`
- `tenant_domains`
- `tenant_user_memberships`

Run them with the landlord connection:

```bash
php artisan migrate --path=database/migrations/landlord --database=landlord
```

## Queue and Service Notes

- jobs that touch tenant data should be tenant-aware
- internal service-to-service calls may pass `X-Tenant`
- tenant database creation itself is not handled by this package

## Local Development

Preferred local URLs:

- `northfield.portal.localhost`
- `northfield.admin.localhost`
- `northfield.lms.localhost`
- `auth.localhost`

If local subdomains are inconvenient, path fallback is available only in `local` and `testing`.

## Testing

```bash
composer test
```

## License

MIT
