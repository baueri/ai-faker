<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests\Support;

use Baueri\AIFaker\Contracts\AIProviderInterface;

final class CountingProvider implements AIProviderInterface
{
    public int $calls = 0;

    public function __construct(private string $response)
    {
    }

    public function generate(string $prompt): string
    {
        $this->calls++;
        return $this->response;
    }
}

