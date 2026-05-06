# Analytica Tenancy Core

Shared Laravel tenancy core package for Analytica applications. This package provides the common package bootstrap and configuration contract that every tenancy-aware Laravel app can install.

## Features

- Laravel package auto-discovery
- Published shared tenancy configuration
- Spatie multitenancy dependency included
- Private Composer VCS installation flow
- Clean package structure aligned with other Analytica Composer packages

## Requirements

- PHP `^8.2`
- Laravel `^12.0`
- `spatie/laravel-multitenancy` `^4.0` is installed as a package dependency

## Installation

### 1. Add the private repository

Add the repository to your app's `composer.json`:

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

Laravel will discover `Analytica\TenancyCore\TenancyCoreServiceProvider` automatically.

### 3. Publish the configuration

```bash
php artisan vendor:publish --tag=tenancy-core-config
```

This publishes the config file to `config/tenancy-core.php`.

## Configuration

Set these values in your `.env` as needed:

```env
TENANCY_CORE_LANDLORD_CONNECTION=landlord
TENANCY_CORE_TENANT_DATABASE_PREFIX=tenant_
TENANCY_CORE_SUBDOMAIN_REGEX=/^(?<tenant>[a-z0-9]+(?:-[a-z0-9]+)*)$/
TENANCY_CORE_CENTRAL_DOMAINS=localhost,127.0.0.1
```

Available config keys:

- `landlord_connection`: central database connection name used for shared tenancy records
- `tenant_database_prefix`: prefix used when building tenant database names
- `subdomain_regex`: regex used to validate or extract tenant subdomains
- `central_domains`: comma-separated env value normalized into an array of non-tenant hosts

## Usage Notes

Version 1 is intentionally bootstrap-focused. It provides the shared package contract and configuration file, but it does not yet include tenant resolution, tenant switching, or application bootstrappers.

## Package Structure

The package keeps a small, explicit structure similar to `analytica-oauth-client`:

- `src/TenancyCoreServiceProvider.php`: package registration and publishing
- `src/config/tenancy-core.php`: shared published config
- `src/Services/TenancyConfigService.php`: tenancy config parsing service used by the package

## Local Verification

For local development, you can also install the package from a Composer path repository:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../analytica/tenancy-core",
      "options": {
        "symlink": true
      }
    }
  ]
}
```

Then run:

```bash
composer require analytica/tenancy-core:*
php artisan vendor:publish --tag=tenancy-core-config
```

## License

MIT
