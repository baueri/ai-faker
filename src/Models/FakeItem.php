<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Models;

class FakeItem
{
    public function __construct(
        public readonly array|string $data
    ) {
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }
}
