<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Cache;

interface CacheInterface
{
    public function get(string $key): ?array;

    public function set(string $key, array $value, ?int $ttl = null): void;
}
