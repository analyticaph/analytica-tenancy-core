<?php

namespace Analytica\TenancyCore\Exceptions;

use RuntimeException;

class MissingTenantException extends RuntimeException
{
    public static function forRequest(): self
    {
        return new self('No tenant could be resolved for this request.');
    }
}
