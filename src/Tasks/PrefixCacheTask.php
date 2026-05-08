<?php

namespace Analytica\TenancyCore\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class PrefixCacheTask implements SwitchTenantTask
{
    protected ?string $originalPrefix = null;

    public function __construct(
        protected bool $enabled = true
    ) {
        $this->originalPrefix = config('cache.prefix');
    }

    public function makeCurrent(IsTenant $tenant): void
    {
        if (! $this->enabled) {
            return;
        }

        $this->setCachePrefix("tenant_{$tenant->getKey()}");
    }

    public function forgetCurrent(): void
    {
        if (! $this->enabled || $this->originalPrefix === null) {
            return;
        }

        $this->setCachePrefix($this->originalPrefix);
    }

    protected function setCachePrefix(string $prefix): void
    {
        config()->set('cache.prefix', $prefix);

        app('cache')->forgetDriver(config('cache.default'));
    }
}
