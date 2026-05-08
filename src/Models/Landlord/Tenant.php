<?php

namespace Analytica\TenancyCore\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Tenant extends \Spatie\Multitenancy\Models\Tenant
{
    use UsesLandlordConnection;

    protected $table = 'tenants';

    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'db_driver',
        'db_charset',
        'db_collation',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(TenantUserMembership::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getConnectionName(): ?string
    {
        return config('multitenancy.landlord_database_connection_name')
            ?: config('tenancy-core.landlord_connection', $this->connection);
    }
}
