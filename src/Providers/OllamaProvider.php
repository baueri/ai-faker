<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Providers;

use Baueri\AIFaker\Contracts\AIProviderInterface;

/**
 * Ollama provider for local models.
 *
 * Requires an Ollama server (default: http://localhost:11434).
 * API: https://github.com/ollama/ollama/blob/main/docs/api.md
 */
class OllamaProvider implements AIProviderInterface
{
    public function __construct(
        protected string $model = 'llama3.1',
        protected string $baseUrl = 'http://localhost:11434',
        protected int $timeout = 30
    ) {}

    /**
     * @throws \Exception
     */
    public function generate(string $prompt): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => rtrim($this->baseUrl, '/') . '/api/generate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]),
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception('Ollama request failed: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \Exception('Invalid JSON response from Ollama');
        }

        if ($httpCode >= 400) {
            $message = $data['error'] ?? 'Unknown Ollama error';
            throw new \Exception("Ollama HTTP error: {$httpCode} ({$message})");
        }

        if (!isset($data['response']) || !is_string($data['response'])) {
            throw new \Exception('Unexpected Ollama response structure');
        }

        return $data['response'];
    }
}

