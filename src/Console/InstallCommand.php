<?php

namespace Analytica\TenancyCore\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'tenancy-core:install';

    protected $description = 'Publish Analytica tenancy core configuration and landlord migrations';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'tenancy-core-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'tenancy-core-multitenancy-config',
            '--force' => false,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'tenancy-core-migrations',
            '--force' => false,
        ]);

        $this->newLine();
        $this->components->info('Analytica tenancy core published successfully.');
        $this->line('Next steps:');
        $this->line('- Add `landlord` and `tenant` connections to `config/database.php`.');
        $this->line('- Set the tenant connection `database` value to `null`.');
        $this->line('- Apply `tenant.context` to tenant business routes and `tenant.member` to authenticated tenant routes.');
        $this->line('- Mark landlord models with `UsesLandlordConnection` and tenant models with `UsesTenantConnection`.');

        return self::SUCCESS;
    }
}
