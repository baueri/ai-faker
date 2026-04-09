<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests;

use Baueri\AIFaker\Models\FakeCollection;
use PHPUnit\Framework\TestCase;

final class FakeCollectionTest extends TestCase
{
    public function testToArrayWorksForStrings(): void
    {
        $c = new FakeCollection(["a", "b"]);
        self::assertSame(["a", "b"], $c->toArray());
        self::assertCount(2, $c);
    }

    public function testToArrayWorksForArrays(): void
    {
        $c = new FakeCollection([["k" => "v"]]);
        self::assertSame([["k" => "v"]], $c->toArray());
        self::assertCount(1, $c);
    }
}

