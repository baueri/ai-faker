<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests;

use Baueri\AIFaker\Parser\ResponseParser;
use PHPUnit\Framework\TestCase;

final class ResponseParserTest extends TestCase
{
    public function testParsesArrayOfStrings(): void
    {
        $parser = new ResponseParser();

        $data = $parser->parse('["a","b","c"]', []);

        self::assertSame(["a", "b", "c"], $data);
    }

    public function testParsesArrayOfObjectsWithRequiredFields(): void
    {
        $parser = new ResponseParser();

        $data = $parser->parse('[{"title":"T","author":"A"}]', ["title", "author"]);

        self::assertSame([["title" => "T", "author" => "A"]], $data);
    }

    public function testThrowsWhenFieldMissing(): void
    {
        $parser = new ResponseParser();

        $this->expectException(\Exception::class);
        $parser->parse('[{"title":"T"}]', ["title", "author"]);
    }

    public function testExtractsJsonFromFencedCodeBlock(): void
    {
        $parser = new ResponseParser();

        $raw = "Sure!\n```json\n[\"x\",\"y\"]\n```\nDone.";
        $data = $parser->parse($raw, []);

        self::assertSame(["x", "y"], $data);
    }

    public function testExtractsFirstBalancedJsonArrayFromText(): void
    {
        $parser = new ResponseParser();

        $raw = "prefix [\"a\"] suffix [\"b\",\"c\"]";
        $data = $parser->parse($raw, []);

        self::assertSame(["a"], $data);
    }
}

