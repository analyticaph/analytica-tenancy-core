<?php

namespace Analytica\TenancyCore\Contracts;

use Illuminate\Http\Request;

interface ResolvesTenantKey
{
    public function name(): string;

    public function resolve(Request $request): ?string;
}
