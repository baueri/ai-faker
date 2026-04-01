<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Generator;

class ResultAggregator
{
    public function __construct(protected int $maxRetries = 3) {}

    public function collect(int $target, callable $generator): array
    {
        $results = [];
        $retries = 0;

        while (count($results) < $target && $retries < $this->maxRetries) {

            $missing = $target - count($results);
            $new = $generator($missing, $results);

            $before = count($results);
            $results = $this->merge($results, $new);
            $after = count($results);

            if ($after <= $before) break;

            $retries++;
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
