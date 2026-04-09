<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests\Support;

use Baueri\AIFaker\Cache\CacheInterface;

final class ArrayCache implements CacheInterface
{
    /** @var array<string, array> */
    private array $store = [];

    public function get(string $key): ?array
    {
        return $this->store[$key] ?? null;
    }

    public function set(string $key, array $value, ?int $ttl = null): void
    {
        $this->store[$key] = $value;
    }
}

