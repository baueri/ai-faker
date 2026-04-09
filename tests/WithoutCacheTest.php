<?php

declare(strict_types=1);

namespace Baueri\AIFaker\Tests;

use Baueri\AIFaker\Generator\Fake;
use Baueri\AIFaker\Tests\Support\ArrayCache;
use Baueri\AIFaker\Tests\Support\CountingProvider;
use PHPUnit\Framework\TestCase;

final class WithoutCacheTest extends TestCase
{
    public function testWithoutCacheIsChainScopedAndDoesNotMutateOriginal(): void
    {
        $provider = new CountingProvider('["x","y"]');
        $cache = new ArrayCache();

        $base = (new Fake($provider, $cache))->for('book')->count(2)->batch(2);

        // With cache: second call should hit cache (no extra provider call).
        $base->generate();
        $base->generate();
        self::assertSame(1, $provider->calls);

        // withoutCache(): should bypass cache and call provider again.
        $base->withoutCache()->generate();
        self::assertSame(2, $provider->calls);

        // Original $base remains cached.
        $base->generate();
        self::assertSame(2, $provider->calls);
    }
}

