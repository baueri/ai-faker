<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests\Support;

use Baueri\AIFaker\Contracts\AIProviderInterface;

final class DummyProvider implements AIProviderInterface
{
    /**
     * @param list<string> $responses
     */
    public function __construct(private array $responses)
    {
    }

    public function generate(string $prompt): string
    {
        if ($this->responses === []) {
            throw new \RuntimeException('No more dummy responses available');
        }

        return array_shift($this->responses);
    }
}

