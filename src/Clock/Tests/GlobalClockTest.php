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
use Rayleigh\Clock\GlobalClock;

#[CoversClass(GlobalClock::class)]
final class GlobalClockTest extends TestCase
{
    /**
     * @after
     * @internal
     */
    #[After]
    public function clearGlobalClock(): void
    {
        GlobalClock::clearGlobalClock();
    }

    #[Test]
    public final function testSetGlobalClock(): void
    {
        $clock = new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        GlobalClock::setGlobalClock($clock);
        self::assertNotSame($clock->now(), GlobalClock::getGlobalClock()->now());
        self::assertNotSame(GlobalClock::getGlobalClock()->now(), GlobalClock::getGlobalClock()->now());
        self::assertSame("2021-01-01 00:00:00", GlobalClock::getGlobalClock()->now()->format("Y-m-d H:i:s"));
    }

    #[Test]
    public final function testSetGlobalClockWithTimezone(): void
    {
        $clock = new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        $timezone = new DateTimeZone("Asia/Tokyo");
        GlobalClock::setGlobalClock($clock, $timezone);
        self::assertNotSame($clock->now(), GlobalClock::getGlobalClock()->now());
        self::assertNotSame(GlobalClock::getGlobalClock()->now(), GlobalClock::getGlobalClock()->now());
        self::assertSame("Asia/Tokyo", GlobalClock::getGlobalClock()->now()->getTimezone()->getName());
    }

    #[Test]
    public final function testSetGlobalClockWithNotForce(): void
    {
        $clock = new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable("2021-01-01 00:00:00");
            }
        };
        GlobalClock::setGlobalClock($clock);
        self::expectExceptionMessage("Global clock is already set");
        GlobalClock::setGlobalClock($clock, null, false);
    }

    #[Test]
    public final function testFailedToGetGlobalClock(): void
    {
        self::expectExceptionMessage("Global clock is not set");
        GlobalClock::getGlobalClock();
    }
}
