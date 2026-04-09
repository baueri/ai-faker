<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

class ResultAggregator
{
    /**
     * @param int $maxRetries Maximum number of attempts (including retries).
     */
    public function __construct(protected int $maxRetries = 3) {}

    /**
     * Collect up to $target unique items from repeated generator calls.
     *
     * The generator is called with ($missing, $existing) and should return an array
     * of items (strings or arrays). Items are merged with basic deduplication.
     *
     * @param int $target
     * @param callable(int, array): array $generator
     * @return array
     */
    public function collect(int $target, callable $generator): array
    {
        $results = [];
        $attempts = 0;
        $noProgress = 0;

        while (count($results) < $target && $attempts < $this->maxRetries) {
            $attempts++;

            $missing = $target - count($results);
            $new = $generator($missing, $results);

            $before = count($results);
            $results = $this->merge($results, $new);
            $after = count($results);

            if ($after <= $before) {
                $noProgress++;
                if ($noProgress >= 2) {
                    break;
                }

                continue;
            }

            $noProgress = 0;
        }

        return array_slice($results, 0, $target);
    }

    protected function merge(array $a, array $b): array
    {
        $map = [];

        foreach (array_merge($a, $b) as $item) {
            $map[md5(json_encode($item))] = $item;
        }

        return array_values($map);
    }
}
