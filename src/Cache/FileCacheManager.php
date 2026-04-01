<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Cache;

class FileCacheManager implements CacheInterface
{
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
    }

    public function get(string $key): ?array
    {
        $file = $this->path . '/' . $key . '.json';

        if (!file_exists($file)) {
            return null;
        }

        return json_decode(file_get_contents($file), true);
    }

    public function set(string $key, array $value, ?int $ttl = null): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        file_put_contents(
            $this->path . '/' . $key . '.json',
            json_encode($value)
        );
    }
}
