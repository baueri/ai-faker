<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Providers;

use Baueri\AIFaker\Contracts\AIProviderInterface;

class GoogleAIStudioProvider implements AIProviderInterface
{
    private const BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * @param string $apiKey Google AI Studio (Gemini) API key.
     * @param string $model Model name, e.g. "gemini-flash-latest".
     * @param int $timeout Request timeout in seconds.
     */
    public function __construct(
        protected string $apiKey,
        protected string $model = 'gemini-flash-latest',
        protected int $timeout = 30
    ) {}

    /**
     * Send prompt to Google AI Studio and return the output text.
     *
     * @throws \Exception
     */
    public function generate(string $prompt): string
    {
        $url = sprintf('%s/%s:generateContent', self::BASE_URL, $this->model);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                "X-goog-api-key: {$this->apiKey}",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
            ]),
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception('Google AI Studio API request failed: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \Exception('Invalid JSON response from Google AI Studio');
        }

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown error';

            throw new \Exception("Google AI Studio API error: {$message}");
        }

        if ($httpCode >= 400) {
            throw new \Exception("Google AI Studio HTTP error: {$httpCode}");
        }

        if (empty($data['candidates'])) {
            throw new \Exception('No candidates returned (possibly blocked)');
        }

        if (
            !isset($data['candidates'][0]['content']['parts'][0]['text']) ||
            !is_string($data['candidates'][0]['content']['parts'][0]['text'])
        ) {
            throw new \Exception('Unexpected Google AI Studio response structure');
        }

        return $data['candidates'][0]['content']['parts'][0]['text'];
    }
}