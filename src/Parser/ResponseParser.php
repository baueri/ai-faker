<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Parser;

class ResponseParser
{
    /**
     * Parse provider output into a PHP array and validate shape.
     *
     * - If $fields is empty: expects a JSON array of strings.
     * - If $fields is non-empty: expects a JSON array of objects containing all required fields.
     *
     * @param string $response Raw model output.
     * @param list<string> $fields Required fields for structured objects.
     * @return array
     *
     * @throws \Exception When JSON is invalid or items do not match the expected shape.
     */
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
        $text = trim($text);

        // Common LLM output format: fenced code blocks.
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $text, $m)) {
            $text = trim($m[1]);
        }

        $start = strpos($text, '[');
        if ($start === false) {
            return $text;
        }

        $inString = false;
        $escape = false;
        $depth = 0;

        $len = strlen($text);
        for ($i = $start; $i < $len; $i++) {
            $ch = $text[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }

                if ($ch === '\\') {
                    $escape = true;
                    continue;
                }

                if ($ch === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($ch === '"') {
                $inString = true;
                continue;
            }

            if ($ch === '[') {
                $depth++;
                continue;
            }

            if ($ch === ']') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $start, $i - $start + 1);
                }
            }
        }

        // Fallback: give parser a chance if output already is JSON.
        return $text;
    }
}
