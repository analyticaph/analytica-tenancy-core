# How to Use analytica-tenancy-core in Your App

This guide walks you through adding multi-tenant database support to any SmartCampus Laravel app using the `analytica-tenancy-core` package.

**What this package does in plain terms:**
When a user visits your app, the package looks at the URL, checks which school (tenant) it belongs to, and automatically connects your app to that school's own database. Every school has its own separate database. The package handles the switching — you just write your app code normally.

---

## Before You Start

Make sure you have:

- PHP 8.2 or higher
- Laravel 12 or higher
- A PostgreSQL database server running
- The `smartcampus_landlord` database already created and migrated (this is the shared database that stores the list of all schools/tenants)

---

## Step 1 — Add the Package to Your App

Open the `composer.json` file of the app you want to add tenancy to (for example `smartcampus-portal`).

**If you are working locally** and the package folder is on the same machine, add this so Composer can find it:

```json
"repositories": [
    {
        "type": "path",
        "url": "../analytica-tenancy-core",
        "options": {
            "symlink": true
        }
    }
]
```

> The `"url"` should be the relative path from your app folder to the package folder.
> `"symlink": true` means any changes you make to the package are immediately reflected in your app — no re-install needed.

**If the package is published on GitHub**, use this instead:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/your-org/analytica-tenancy-core"
    }
]
```

Then in the `"require"` section, add the package:

```json
"require": {
    "analytica/tenancy-core": "dev-main as 1.0.0"
}
```

Then run in your terminal:

```bash
composer install
```

That's it — Laravel will automatically detect the package and activate it. You don't need to add anything to `config/app.php`.

---

## Step 2 — Add Two Database Connections

Your app needs two database connections:

- **`landlord`** — connects to the shared database that has the list of all schools
- **`tenant`** — connects to whichever school's database is currently being accessed (the package switches this automatically)

Open `config/database.php` and add both inside the `'connections'` array:

```php
'connections' => [

    // ... your existing connections above ...

    'landlord' => [
        'driver'        => 'pgsql',
        'host'          => env('LANDLORD_DB_HOST', '127.0.0.1'),
        'port'          => env('LANDLORD_DB_PORT', '5432'),
        'database'      => env('LANDLORD_DB_DATABASE', 'smartcampus_landlord'),
        'username'      => env('LANDLORD_DB_USERNAME', 'postgres'),
        'password'      => env('LANDLORD_DB_PASSWORD', ''),
        'charset'       => 'utf8',
        'prefix'        => '',
        'prefix_indexes'=> true,
        'search_path'   => 'public',
        'sslmode'       => 'prefer',
    ],

    'tenant' => [
        'driver'        => 'pgsql',
        'host'          => env('TENANT_DB_HOST', '127.0.0.1'),
        'port'          => env('TENANT_DB_PORT', '5432'),
        'database'      => env('TENANT_DB_DATABASE', ''),
        'username'      => env('TENANT_DB_USERNAME', 'postgres'),
        'password'      => env('TENANT_DB_PASSWORD', ''),
        'charset'       => 'utf8',
        'prefix'        => '',
        'prefix_indexes'=> true,
        'search_path'   => 'public',
        'sslmode'       => 'prefer',
    ],

],
```

> Notice that `tenant.database` is intentionally left empty. The package fills it in at runtime based on which school is being visited.

---

## Step 3 — Add Environment Variables

Open your `.env` file and add these lines:

```env
# Shared landlord database (list of all schools)
LANDLORD_DB_HOST=127.0.0.1
LANDLORD_DB_PORT=5432
LANDLORD_DB_DATABASE=smartcampus_landlord
LANDLORD_DB_USERNAME=smartcampus_user
LANDLORD_DB_PASSWORD=SmartCampus_2025!

# Tenant database defaults (the package overwrites these per request)
TENANT_DB_HOST=127.0.0.1
TENANT_DB_PORT=5432
TENANT_DB_DATABASE=
TENANT_DB_USERNAME=smartcampus_user
TENANT_DB_PASSWORD=SmartCampus_2025!
```

---

## Step 4 — Run the Landlord Migrations

The package comes with migrations that create the tables needed to store the list of schools, their domains, and their users.

Run this once:

```bash
php artisan vendor:publish --tag=tenancy-core-migrations
php artisan migrate --path=database/migrations/landlord --database=landlord
```

> You only need to do this once per app. Skip this step if the `tenants`, `tenant_domains`, and `tenant_user_memberships` tables already exist in your landlord database.

---

## Step 5 — Protect Your Routes

Any route that should only work when a valid school is detected needs the `tenant.context` middleware. Add it to your route groups in `routes/web.php` or `routes/api.php`:

```php
Route::middleware('tenant.context')->group(function () {
    Route::get('/dashboard', DashboardController::class);
    Route::get('/students', StudentController::class);
    Route::get('/courses', CourseController::class);
    // ... all your school-specific routes
});
```

> If someone visits a URL that doesn't match any school, the package returns a 404 automatically.

If you also want to make sure the logged-in user is a member of the current school (not just any school), add `tenant.member` too:

```php
Route::middleware(['tenant.context', 'auth', 'tenant.member'])->group(function () {
    Route::get('/dashboard', DashboardController::class);
});
```

---

## Step 6 — Use the Tenant Database in Your Models

Tell each of your models to use the `tenant` connection so they always read from the current school's database:

```php
class Student extends Model
{
    protected $connection = 'tenant';
}

class Course extends Model
{
    protected $connection = 'tenant';
}
```

Any model that stores shared data (data that belongs to all schools, not one school) should use the `landlord` connection:

```php
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class GlobalSetting extends Model
{
    use UsesLandlordConnection;
}
```

---

## Step 7 — How the Package Knows Which School to Use

When a request comes in, the package checks three things in order:

### Option A — Subdomain (recommended for production)

If a user visits `northfield.portal.yourapp.com`, the package reads `northfield` from the subdomain and looks it up in the landlord database.

Set the base domains in your `.env`:

```env
TENANCY_CORE_BASE_DOMAINS=portal.yourapp.com,admin.yourapp.com
```

### Option B — Request Header (for internal API calls between services)

If another service is calling your app and sends an `X-Tenant: northfield` header, the package picks it up from there.

### Option C — URL Path (for local development only)

If you visit `http://localhost:8000/t/northfield/dashboard`, the package reads `northfield` from the path. This only works in `local` and `testing` environments — it will not work in production.

Set which domains are your central (non-tenant) domains so the package skips them:

```env
TENANCY_CORE_CENTRAL_DOMAINS=localhost,127.0.0.1
```

---

## Step 8 — Get the Current School in Your Code

After the package has resolved the tenant, you can access it anywhere in your controllers or services:

```php
// Get the current tenant (school) object
$school = app('currentTenant');

echo $school->name;     // "Northfield Academy"
echo $school->slug;     // "northfield"
echo $school->database; // "tenant_northfield"

// Or using the helper functions
currentTenantId();   // the school's ID
currentTenantSlug(); // "northfield"
```

---

## How It All Fits Together

Here is the full flow from a user opening their browser to data being returned:

```
User visits northfield.portal.yourapp.com/dashboard
        ↓
Package reads "northfield" from the subdomain
        ↓
Looks up "northfield" in the landlord database → finds the school
        ↓
Switches the "tenant" database connection to tenant_northfield
        ↓
tenant.context middleware confirms a school was found
        ↓
Your DashboardController runs
        ↓
DB::connection('tenant') → reads from tenant_northfield
        ↓
Returns Northfield Academy's data only
```

Every request is fully isolated. Northfield's data never mixes with Riverside's data because they are on completely separate databases.

---

## Quick Checklist

Use this to confirm your setup is complete before testing:

- [ ] Package added to `composer.json` and `composer install` was run
- [ ] `landlord` and `tenant` connections added to `config/database.php`
- [ ] Landlord DB variables set in `.env`
- [ ] Tenant DB variables set in `.env` (database left blank)
- [ ] Landlord migrations published and run
- [ ] School routes wrapped with `middleware('tenant.context')`
- [ ] Your models have `protected $connection = 'tenant'`
- [ ] At least one active tenant exists in the landlord database

---

## Testing That It Works

**Locally using the path resolver:**

1. Start your app with `php artisan serve`
2. Visit `http://localhost:8000/t/northfield/dashboard`
3. The package resolves `northfield`, switches the database, and your controller runs against `tenant_northfield`

**In HeidiSQL or any database client:**

Each school's database is a separate database on the same server. Connect using the same credentials — just change the database name:

| Field    | Value                          |
|----------|-------------------------------|
| Host     | `127.0.0.1`                   |
| Port     | `5432`                        |
| User     | `smartcampus_user`            |
| Password | `SmartCampus_2025!`           |
| Database | `tenant_northfield` or `tenant_schoola` etc. |

---

## Common Problems

**"Tenant not found" or 404 on every request**
- The school slug in the URL does not match any `slug` in the `tenants` table
- The tenant's `status` is not `active`
- The `TENANCY_CORE_CENTRAL_DOMAINS` list includes a domain it should not

**"Connection refused" or database errors**
- The `tenant` connection is trying to connect before the package has set the database name
- Make sure tenant routes are inside `middleware('tenant.context')` so the switch happens first

**"Password authentication failed for user postgres"**
- The `TENANT_DB_USERNAME` or `TENANT_DB_PASSWORD` in `.env` is wrong or missing
- Leave those fields blank in the Create Tenant form — the package fills them from `.env`
