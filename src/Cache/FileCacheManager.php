<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Cache;

class FileCacheManager implements CacheInterface
{
    protected string $path;

    /**
     * @param string $path Directory path where cache files will be stored.
     */
    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
    }

    /**
     * Retrieve a cached parsed JSON array by key.
     */
    public function get(string $key): ?array
    {
        $file = $this->path . '/' . $key . '.json';

        if (!file_exists($file)) {
            return null;
        }

        return json_decode(file_get_contents($file), true);
    }

    /**
     * Store a parsed JSON array by key.
     *
     * @param string $key
     * @param array $value
     * @param int|null $ttl Not currently used by this implementation.
     */
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
