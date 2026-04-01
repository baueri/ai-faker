<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Parser;

class ResponseParser
{
    public function parse(string $response, array $fields): array
    {
        $json = $this->extractJson($response);

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new \Exception('Invalid JSON');
        }

        if (empty($fields)) {
            foreach ($data as $item) {
                if (!is_string($item)) {
                    throw new \Exception('Expected array of strings');
                }
            }

            return $data;
        }

        foreach ($data as $item) {
            if (!is_array($item)) {
                throw new \Exception('Expected array item');
            }

            foreach ($fields as $field) {
                if (!array_key_exists($field, $item)) {
                    throw new \Exception("Missing field: {$field}");
                }
            }
        }

        return $data;
    }

    protected function extractJson(string $text): string
    {
        if (preg_match('/\[.*\]/s', $text, $matches)) {
            return $matches[0];
        }

        return $text;
    }
}
