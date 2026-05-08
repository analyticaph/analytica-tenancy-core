# Analytica Tenancy Core v2 Implementation Guide

This file explains what was changed in `analytica-tenancy-core`, why it was changed, and what each file is for.

The goal of this upgrade was simple:

- before: this package only published a small config file
- now: this package can help Laravel apps find the current tenant, switch to the right tenant database, check tenant membership, and publish the shared landlord tables

## Big Picture

In your setup:

- `auth-service` stays central
- business apps like `admin`, `portal`, and `lms` need to know which school is being opened
- once the school is known, the app should switch to that school's database

This package now gives all Laravel apps one shared way to do that.

## Main Changes by Area

### 1. Package setup files

#### [composer.json](./composer.json)
Why it was changed:

- the package now contains many more classes than before
- it also now has tests

What changed:

- added test packages
- added a `test` script
- added `src/helpers.php` to autoload so helper functions are always available
- added test namespace autoloading

Why this matters:

- the package can now be tested on its own
- helper functions work without extra setup in host apps

#### [composer.lock](./composer.lock)
Why it exists now:

- Composer created it when dependencies were installed

What it does:

- locks the exact package versions used during testing

Why this matters:

- makes local package testing more repeatable

#### [phpunit.xml.dist](./phpunit.xml.dist)
Why it was added:

- the package now has its own test suite

What it does:

- tells PHPUnit where the tests are

Why this matters:

- running `composer test` works cleanly inside this package

### 2. Service provider and bootstrapping

#### [src/TenancyCoreServiceProvider.php](./src/TenancyCoreServiceProvider.php)
Why it was changed:

- this is the main entry point of the package
- it needed to do much more than just publish one config file

What it does now:

- loads both `tenancy-core` and `multitenancy` config
- registers the current tenant helper service
- binds the shared tenant model
- registers the install command
- adds middleware aliases:
  - `tenant.context`
  - `tenant.member`
- publishes:
  - package config
  - multitenancy config
  - landlord migrations

Why this matters:

- host Laravel apps can install the package and immediately get the shared tenancy pieces

### 3. Config files

#### [src/config/tenancy-core.php](./src/config/tenancy-core.php)
Why it was changed:

- the old file only had a few basic values
- the new package needs more control over how tenants are found and how runtime behavior works

What it now stores:

- landlord connection name
- tenant connection name
- tenant database prefix
- subdomain pattern
- central domains
- tenant resolver order
- header name for internal requests
- path prefix for local fallback
- base domains
- missing-tenant behavior
- cache prefix setting
- permission cache reset setting

Why this matters:

- apps can change behavior without editing package code

#### [src/config/multitenancy.php](./src/config/multitenancy.php)
Why it was added:

- Spatie multitenancy needs its own config
- this package now gives a shared default version for all apps

What it does:

- points Spatie to the custom tenant finder
- tells Spatie which model is the tenant model
- sets landlord and tenant connection names
- lists the tasks to run when a tenant becomes current

Why this matters:

- every app can use the same shared multitenancy setup instead of building its own version

### 4. Small support utilities

#### [src/Services/TenancyConfigService.php](./src/Services/TenancyConfigService.php)
Why it was changed:

- the old version only cleaned up central domain values
- the new config has more values that need cleanup

What it does now:

- parses domain lists
- parses resolver order lists
- normalizes path prefixes
- normalizes host names

Why this matters:

- config values are kept clean and consistent

#### [src/helpers.php](./src/helpers.php)
Why it was added:

- it is useful for host apps to quickly ask for tenant info

What it adds:

- `currentTenantId()`
- `currentTenantSlug()`

Why this matters:

- app code can read the current tenant more easily

#### [src/Support/CurrentTenant.php](./src/Support/CurrentTenant.php)
Why it was added:

- helper functions need a shared service behind them

What it does:

- returns the current tenant
- returns the current tenant id
- returns the current tenant slug
- exposes landlord and tenant connection names

Why this matters:

- this gives host apps one simple place to get tenant context

### 5. How a tenant is found

#### [src/Contracts/ResolvesTenantKey.php](./src/Contracts/ResolvesTenantKey.php)
Why it was added:

- the package now supports several ways to find a tenant

What it does:

- defines a common shape for tenant key resolvers

Why this matters:

- each resolver follows the same pattern and is easier to extend later

#### [src/Resolvers/HostTenantKeyResolver.php](./src/Resolvers/HostTenantKeyResolver.php)
Why it was added:

- production requests should usually find the tenant from the host or subdomain

What it does:

- reads the request host
- ignores central domains
- checks base domains like `portal.localhost`
- extracts the tenant slug from hosts like `northfield.portal.localhost`

Why this matters:

- this is the main way tenant apps know which school is being opened

#### [src/Resolvers/HeaderTenantKeyResolver.php](./src/Resolvers/HeaderTenantKeyResolver.php)
Why it was added:

- some internal requests are not normal browser requests

What it does:

- reads a header such as `X-Tenant`

Why this matters:

- service-to-service calls can still carry tenant context

#### [src/Resolvers/PathTenantKeyResolver.php](./src/Resolvers/PathTenantKeyResolver.php)
Why it was added:

- local browser testing is sometimes easier with a URL path fallback

What it does:

- reads tenant values from paths like `/t/northfield/...`
- only works in `local` or `testing`

Why this matters:

- local development is easier without changing the production design

#### [src/TenantFinder/SmartCampusTenantFinder.php](./src/TenantFinder/SmartCampusTenantFinder.php)
Why it was added:

- Spatie needs one class that decides which tenant belongs to a request

What it does:

- tries the configured resolvers in order
- looks up a tenant by slug, database name, or known domain
- rejects inactive tenants

Why this matters:

- all tenant-aware apps can use one shared tenant lookup flow

### 6. Landlord models

These models use the landlord database, not the tenant database.

#### [src/Models/Landlord/Tenant.php](./src/Models/Landlord/Tenant.php)
Why it was added:

- the package needs a shared tenant model for all apps

What it does:

- represents one tenant or school
- stores the database details needed for switching
- links to tenant domains
- links to tenant memberships
- knows whether the tenant is active

Why this matters:

- the package needs one shared source of truth for tenant records

#### [src/Models/Landlord/TenantDomain.php](./src/Models/Landlord/TenantDomain.php)
Why it was added:

- one tenant may have one or more known domains or host values

What it does:

- stores host or domain entries for a tenant

Why this matters:

- tenant lookup can work from known host names

#### [src/Models/Landlord/TenantUserMembership.php](./src/Models/Landlord/TenantUserMembership.php)
Why it was added:

- authenticated users may or may not belong to a tenant

What it does:

- stores the link between a user and a tenant

Why this matters:

- apps can block users from opening a tenant they do not belong to

### 7. Middleware

#### [src/Http/Middleware/NeedsTenantContext.php](./src/Http/Middleware/NeedsTenantContext.php)
Why it was added:

- some routes should never run unless a tenant is already known

What it does:

- checks whether the current tenant is set
- throws an error or returns `404` when missing

Why this matters:

- business routes do not continue blindly without tenant context

#### [src/Http/Middleware/EnsureValidTenantUserMembership.php](./src/Http/Middleware/EnsureValidTenantUserMembership.php)
Why it was added:

- knowing the tenant is not enough
- the logged-in user must also belong to that tenant

What it does:

- checks the landlord membership table
- allows the request only if the user belongs to the tenant

Why this matters:

- this protects tenant data from users who should not see it

### 8. Tenant switch tasks

These run when a tenant becomes the current tenant.

#### [src/Tasks/SwitchTenantDatabaseTask.php](./src/Tasks/SwitchTenantDatabaseTask.php)
Why it was added:

- switching tenants means switching database connection details

What it does:

- updates the tenant connection config
- swaps in the correct database name
- can also swap host, port, username, password, and related values
- reconnects the database connection
- restores the original connection when the tenant is cleared

Why this matters:

- without this file, the app would still talk to the wrong database

#### [src/Tasks/PrefixCacheTask.php](./src/Tasks/PrefixCacheTask.php)
Why it was added:

- cache values from one tenant should not mix with another tenant

What it does:

- changes the cache prefix when a tenant is active
- resets the prefix when the tenant is cleared

Why this matters:

- cached data stays separated by tenant

#### [src/Tasks/ResetPermissionCacheTask.php](./src/Tasks/ResetPermissionCacheTask.php)
Why it was added:

- permission data can also stay cached in memory

What it does:

- clears permission cache when tenant context changes

Why this matters:

- permission checks are less likely to use stale data from another tenant

### 9. Exceptions

These files give clear errors for common tenant problems.

#### [src/Exceptions/MissingTenantException.php](./src/Exceptions/MissingTenantException.php)
Why it was added:

- for requests where no tenant could be found

#### [src/Exceptions/InactiveTenantException.php](./src/Exceptions/InactiveTenantException.php)
Why it was added:

- for tenants that exist but should not be used

#### [src/Exceptions/InvalidTenantUserMembershipException.php](./src/Exceptions/InvalidTenantUserMembershipException.php)
Why it was added:

- for users who do not belong to the current tenant

Why these matter:

- problems are easier to understand and handle

### 10. Console command

#### [src/Console/InstallCommand.php](./src/Console/InstallCommand.php)
Why it was added:

- package setup should be simple for host apps

What it does:

- publishes config files
- publishes landlord migrations
- prints the next setup steps

Why this matters:

- install flow is easier and more consistent

### 11. Landlord migrations

These files create the shared tables used by the package.

#### [database/migrations/landlord/2026_05_08_000001_create_tenants_table.php](./database/migrations/landlord/2026_05_08_000001_create_tenants_table.php)
Why it was added:

- the package needs a shared tenant table

What it creates:

- tenant name
- slug
- status
- database connection details
- settings

#### [database/migrations/landlord/2026_05_08_000002_create_tenant_domains_table.php](./database/migrations/landlord/2026_05_08_000002_create_tenant_domains_table.php)
Why it was added:

- tenants need host or domain records

What it creates:

- tenant-to-domain mapping

#### [database/migrations/landlord/2026_05_08_000003_create_tenant_user_memberships_table.php](./database/migrations/landlord/2026_05_08_000003_create_tenant_user_memberships_table.php)
Why it was added:

- apps need a shared tenant membership table

What it creates:

- tenant-to-user membership mapping

Why these matter:

- host apps can publish and run the same landlord schema everywhere

### 12. Tests

The package now has its own test suite.

#### [tests/TestCase.php](./tests/TestCase.php)
Why it was added:

- tests need a shared base setup

What it does:

- loads the package provider
- loads the Spatie provider
- sets simple package config for tests
- provides a helper to make fake tenant objects

#### [tests/Unit/TenancyConfigServiceTest.php](./tests/Unit/TenancyConfigServiceTest.php)
Why it was added:

- checks config parsing behavior

#### [tests/Feature/SmartCampusTenantFinderTest.php](./tests/Feature/SmartCampusTenantFinderTest.php)
Why it was added:

- checks tenant lookup from:
  - host
  - subdomain
  - header
  - local path
- also checks inactive tenant handling

#### [tests/Feature/TenantMiddlewareTest.php](./tests/Feature/TenantMiddlewareTest.php)
Why it was added:

- checks the tenant-required middleware
- checks tenant membership middleware

#### [tests/Feature/SwitchTenantTasksTest.php](./tests/Feature/SwitchTenantTasksTest.php)
Why it was added:

- checks database switch config changes
- checks cache prefix behavior

#### [tests/Feature/TenantModelTest.php](./tests/Feature/TenantModelTest.php)
Why it was added:

- checks that the landlord tenant model points to the landlord connection

Why these tests matter:

- they protect the shared package from breaking as it grows

### 13. Documentation

#### [README.md](./README.md)
Why it was changed:

- the old README only described a small bootstrap package
- that was no longer true

What it now explains:

- what the package does
- how to install it
- which config values matter
- what host apps must add
- how tenant lookup works
- which middleware aliases are available
- which helper functions are available
- how to run landlord migrations

Why this matters:

- developers can understand how to use the package without reading all the source files first

## How the Request Flow Works Now

This is the simple request flow after the upgrade:

1. A request comes into a tenant app like `northfield.portal.localhost`.
2. `SmartCampusTenantFinder` tries to find the tenant.
3. It usually finds the tenant from the host name.
4. The package sets the current tenant.
5. The database switch task points the tenant connection to Northfield's database.
6. Cache and permission state are refreshed for that tenant.
7. `tenant.context` makes sure the route has a tenant.
8. `tenant.member` can make sure the user belongs to that tenant.
9. The app continues using the correct tenant database.

## Why This Upgrade Was Needed

Without this upgrade:

- each app would need to build its own tenant-finding logic
- each app might switch databases in a slightly different way
- membership checks could become inconsistent
- landlord schema could drift between projects

With this upgrade:

- the shared rules live in one package
- the business apps can stay smaller
- tenant behavior is more consistent across the ecosystem

## Notes

- `.phpunit.result.cache` was created by the test run. It is not part of the package logic.
- `vendor/` and `composer.lock` appeared because package dependencies were installed locally for testing.
