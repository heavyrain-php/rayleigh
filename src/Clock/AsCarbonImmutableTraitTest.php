<?php

declare(strict_types=1);

/**
 * Class AsCarbonImmutableTraitTest
 * @package Rayleigh\Clock
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Clock;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

#[CoversClass(AsCarbonImmutableTrait::class)]
final class AsCarbonImmutableTraitTest extends TestCase
{
    #[Test]
    public function testAsCarbonImmutable(): void
    {
        $clock = new class implements ClockInterface
        {
            use AsCarbonImmutableTrait;

            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        self::assertInstanceOf(CarbonImmutable::class, $clock->asCarbonImmutable());
        self::assertSame("2021-01-01 00:00:00", $clock->asCarbonImmutable()->format("Y-m-d H:i:s"));
    }
}
