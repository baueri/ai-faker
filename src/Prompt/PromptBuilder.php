<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Prompt;

class PromptBuilder
{
    public function build(array $state, bool $isRetry = false): string
    {
        $lines = [];

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

        if (!empty($state['constraints'])) {
            $lines[] = "Constraints:";
            foreach ($state['constraints'] as $key => $value) {
                $lines[] = "- {$key}: " . (is_array($value) ? implode(', ', $value) : $value);
            }
        }

        $lines[] = "Fields:";
        foreach ($state['fields'] as $field) {
            $lines[] = "- {$field}";
        }

        $lines[] = "Return ONLY valid JSON array.";

        return implode("\n", $lines);
    }
}
