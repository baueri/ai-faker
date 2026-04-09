<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Cache;

interface CacheInterface
{
    /**
     * @return array|null Parsed JSON array previously stored, or null if missing.
     */
    public function get(string $key): ?array;

    /**
     * @param array $value Parsed JSON array to cache.
     * @param int|null $ttl Optional TTL (implementation-specific, may be ignored).
     */
    public function set(string $key, array $value, ?int $ttl = null): void;
}
