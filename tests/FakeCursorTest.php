<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests;

use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Tests\Support\ArrayCache;
use Baueri\AIFaker\Tests\Support\DummyProvider;
use PHPUnit\Framework\TestCase;

final class FakeCursorTest extends TestCase
{
    public function testIteratesOverStringItemsWithoutTypeError(): void
    {
        $provider = new DummyProvider([
            '["a","b","c"]',
        ]);

        $fake = new Fake($provider, null);
        $cursor = $fake->for('words')->count(3)->batch(3)->cursor();

        $items = [];
        foreach ($cursor as $item) {
            $items[] = $item;
        }

        self::assertSame(["a", "b", "c"], $items);
    }

    public function testBatchingDoesNotRepeatDueToCacheKeyCollision(): void
    {
        $provider = new DummyProvider([
            '["a1","a2","a3","a4","a5"]',
            '["b1","b2","b3","b4","b5"]',
            '["c1","c2","c3","c4","c5"]',
            '["d1","d2","d3","d4","d5"]',
        ]);

        $fake = new Fake($provider, new ArrayCache());

        $cursor = $fake
            ->for('book')
            ->count(20)
            ->batch(5)
            ->cursor();

        $items = [];
        while ($item = $cursor->fetch()) {
            $items[] = $item;
        }

        self::assertCount(20, $items);
        self::assertSame(20, count(array_unique($items)));
        self::assertSame("a1", $items[0]);
        self::assertSame("d5", $items[19]);
    }
}

