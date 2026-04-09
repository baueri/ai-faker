<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Prompt;

class PromptBuilder
{
    /**
     * Build a plain-text prompt for the provider.
     *
     * Expected output: the model should return ONLY a valid JSON array:
     * - string mode: ["a","b",...]
     * - fields mode: [{"field":...}, ...]
     *
     * @param array<string, mixed> $state
     */
    public function build(array $state, bool $isRetry = false): string
    {
        $lines = [];

        if (isset($state['offset']) && is_int($state['offset'])) {
            $start = $state['offset'] + 1;
            $end = $state['offset'] + (int)($state['count'] ?? 0);
            if ($end >= $start) {
                $lines[] = "Item range: {$start}-{$end}.";
            }
        }

        $lines[] = $isRetry
            ? "Generate {$state['count']} MORE items for {$state['domain']}."
            : "Generate {$state['count']} items for {$state['domain']}.";

        if ($isRetry && !empty($state['existing'])) {
            $lines[] = "Do NOT repeat these:";
            $lines[] = json_encode($state['existing']);
        }

        if (!empty($state['language'])) {
            $lines[] = "Language: {$state['language']}.";
        }

        if (!empty($state['tone'])) {
            $lines[] = "Tone: {$state['tone']}.";
        }

        if (!empty($state['context'])) {
            $lines[] = "context:";
            foreach ($state['context'] as $key => $value) {
                $lines[] = "- {$key}: " . (is_array($value) ? implode(', ', $value) : $value);
            }
        }

        if (! empty($state['fields'])) {
            $lines[] = "Fields:";
            foreach ($state['fields'] as $field) {
                $lines[] = "- {$field}";
            }
        } else {
            $lines[] = "Each array element is a string.";
        }

        $lines[] = "Return ONLY valid JSON array.";

        return implode("\n", $lines);
    }
}
