<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Contracts;

interface AIProviderInterface
{
    /**
     * Execute a prompt and return the raw model text output.
     *
     * The output is expected to contain a JSON array (optionally wrapped in extra text),
     * which will be extracted/parsed by the library.
     */
    public function generate(string $prompt): string;
}