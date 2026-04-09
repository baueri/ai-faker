<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests;

use Baueri\AIFaker\Generator\ResultAggregator;
use PHPUnit\Framework\TestCase;

final class ResultAggregatorTest extends TestCase
{
    public function testRetriesWhenFirstAttemptHasNoProgress(): void
    {
        $agg = new ResultAggregator(3);

        $calls = 0;
        $out = $agg->collect(2, function (int $missing, array $existing) use (&$calls): array {
            $calls++;

            // First attempt returns nothing (no progress), second provides 2 items.
            if ($calls === 1) {
                return [];
            }

            return ["a", "b"];
        });

        self::assertSame(["a", "b"], $out);
        self::assertSame(2, $calls);
    }

    public function testStopsAfterConsecutiveNoProgress(): void
    {
        $agg = new ResultAggregator(10);

        $calls = 0;
        $out = $agg->collect(2, function () use (&$calls): array {
            $calls++;
            return [];
        });

        self::assertSame([], $out);
        self::assertSame(2, $calls);
    }

    public function testDeduplicatesResults(): void
    {
        $agg = new ResultAggregator(3);

        $out = $agg->collect(2, function (): array {
            return ["x", "x", "y"];
        });

        self::assertSame(["x", "y"], $out);
    }
}

