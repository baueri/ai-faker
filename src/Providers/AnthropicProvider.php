<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Providers;

use Baueri\AIFaker\Contracts\AIProviderInterface;

/**
 * Anthropic (Claude) provider using the Messages API.
 *
 * Docs: https://docs.anthropic.com/en/api/messages
 */
class AnthropicProvider implements AIProviderInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'claude-3-5-haiku-latest',
        protected int $timeout = 30,
        protected string $anthropicVersion = '2023-06-01',
        protected int $maxTokens = 2048
    ) {}

    /**
     * @throws \Exception
     */
    public function generate(string $prompt): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . $this->anthropicVersion,
                'content-type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]),
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception('Anthropic request failed: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \Exception('Invalid JSON response from Anthropic');
        }

        if ($httpCode >= 400) {
            $message = $data['error']['message'] ?? $data['message'] ?? 'Unknown Anthropic error';
            throw new \Exception("Anthropic HTTP error: {$httpCode} ({$message})");
        }

        if (!isset($data['content']) || !is_array($data['content']) || $data['content'] === []) {
            throw new \Exception('Unexpected Anthropic response structure');
        }

        $first = $data['content'][0] ?? null;
        if (!is_array($first) || ($first['type'] ?? null) !== 'text' || !is_string($first['text'] ?? null)) {
            throw new \Exception('Unexpected Anthropic content structure');
        }

        return $first['text'];
    }
}

