<?php

declare(strict_types=1);

/**
 * Class ClockTest
 * @package Rayleigh\Clock
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Clock\Tests;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Rayleigh\Clock\FixedClock;

#[CoversClass(FixedClock::class)]
final class FixedClockTest extends TestCase
{
    /**
     * @after
     * @internal
     */
    #[After]
    public function clearFixedClock(): void
    {
        FixedClock::clearFixedClock();
    }

    #[Test]
    public final function testSetFixedClock(): void
    {
        $clock = new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        FixedClock::setFixedClock($clock);
        self::assertNotSame($clock->now(), FixedClock::getFixedClock()->now());
        self::assertNotSame(FixedClock::getFixedClock()->now(), FixedClock::getFixedClock()->now());
        self::assertSame("2021-01-01 00:00:00", FixedClock::getFixedClock()->now()->format("Y-m-d H:i:s"));
    }

    #[Test]
    public final function testSetFixedClockWithTimezone(): void
    {
        $clock = new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        $timezone = new DateTimeZone("Asia/Tokyo");
        FixedClock::setFixedClock($clock, $timezone);
        self::assertNotSame($clock->now(), FixedClock::getFixedClock()->now());
        self::assertNotSame(FixedClock::getFixedClock()->now(), FixedClock::getFixedClock()->now());
        self::assertSame("Asia/Tokyo", FixedClock::getFixedClock()->now()->getTimezone()->getName());
    }

    #[Test]
    public final function testSetFixedClockWithNotForce(): void
    {
        $clock = new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        FixedClock::setFixedClock($clock);
        self::expectExceptionMessage("Fixed clock is already set");
        FixedClock::setFixedClock($clock, null, false);
    }

    #[Test]
    public final function testFailedToGetFixedClock(): void
    {
        self::expectExceptionMessage("Fixed clock is not set");
        FixedClock::getFixedClock();
    }
}
