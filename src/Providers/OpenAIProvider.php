<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Providers;

use Baueri\AIFaker\Contracts\AIProviderInterface;

class OpenAIProvider implements AIProviderInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'gpt-4.1-mini',
        protected int $timeout = 30
    ) {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generate(string $prompt): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.openai.com/v1/responses',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->model,
                'input' => $prompt,
            ]),
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception('OpenAI request failed: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \Exception('Invalid JSON response from OpenAI');
        }

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown OpenAI error';
            $type = $data['error']['type'] ?? null;
            $code = $data['error']['code'] ?? null;

            throw new \Exception(sprintf(
                'OpenAI API error (%s%s): %s',
                $type ?? 'unknown',
                $code ? " / {$code}" : '',
                $message
            ));
        }

        if ($httpCode >= 400) {
            throw new \Exception("OpenAI HTTP error: {$httpCode}");
        }

        if (
            !isset($data['output'][0]['content'][0]['text']) ||
            !is_string($data['output'][0]['content'][0]['text'])
        ) {
            throw new \Exception('Unexpected OpenAI response structure');
        }

        return $data['output'][0]['content'][0]['text'];
    }
}
