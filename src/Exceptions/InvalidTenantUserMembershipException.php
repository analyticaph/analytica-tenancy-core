<?php

namespace Analytica\TenancyCore\Exceptions;

use RuntimeException;

class InvalidTenantUserMembershipException extends RuntimeException
{
    public static function forUser(int|string|null $userId, int|string|null $tenantId): self
    {
        return new self("User [{$userId}] does not belong to tenant [{$tenantId}].");
    }
}
