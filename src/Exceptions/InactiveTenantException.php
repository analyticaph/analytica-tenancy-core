<?php

namespace Analytica\TenancyCore\Exceptions;

use RuntimeException;

class InactiveTenantException extends RuntimeException
{
    public static function forIdentifier(string $identifier): self
    {
        return new self("Tenant [{$identifier}] is inactive.");
    }
}
