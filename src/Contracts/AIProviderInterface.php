<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Contracts;

interface AIProviderInterface
{
    public function generate(string $prompt): string;
}