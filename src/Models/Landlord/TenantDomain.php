<?php

namespace Analytica\TenancyCore\Models\Landlord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class TenantDomain extends Model
{
    use UsesLandlordConnection;

    protected $table = 'tenant_domains';

    protected $connection = 'landlord';

    protected $fillable = [
        'tenant_id',
        'domain',
        'type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'bool',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getConnectionName(): ?string
    {
        return config('multitenancy.landlord_database_connection_name')
            ?: config('tenancy-core.landlord_connection', $this->connection);
    }
}
