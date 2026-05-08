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
        return self::parseDomainList($domains);
    }

    /**
     * Normalize a comma-separated list into a clean string array.
     *
     * @return array<int, string>
     */
    public static function parseDomainList(string $values): array
    {
        return array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', $values)
        )));
    }

    /**
     * Normalize a comma-separated resolver order.
     *
     * @return array<int, string>
     */
    public static function parseResolverOrder(string $resolvers): array
    {
        $parsed = self::parseDomainList($resolvers);

        return $parsed === [] ? ['host', 'header', 'path'] : $parsed;
    }

    /**
     * Normalize the path prefix so resolver code can safely compare segments.
     */
    public static function normalizePathPrefix(string $pathPrefix): string
    {
        $trimmed = trim($pathPrefix);

        if ($trimmed === '') {
            return '/t';
        }

        return '/'.trim($trimmed, '/');
    }

    /**
     * Normalize host names for matching.
     */
    public static function normalizeHost(string $host): string
    {
        return strtolower(trim($host));
    }
}
