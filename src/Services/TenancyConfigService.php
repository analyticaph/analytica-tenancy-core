<?php

namespace Analytica\TenancyCore\Services;

class TenancyConfigService
{
    /**
     * Normalize a comma-separated env value into a clean domain array.
     *
     * @return array<int, string>
     */
    public static function parseCentralDomains(string $domains): array
    {
        return array_values(array_filter(array_map(
            static fn (string $domain): string => trim($domain),
            explode(',', $domains)
        )));
    }
}
