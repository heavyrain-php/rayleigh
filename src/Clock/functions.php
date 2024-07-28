<?php

declare(strict_types=1);

namespace Rayleigh\Clock;

use DateTimeImmutable;

/**
 * Class ClockTest
 * @package Rayleigh\Clock
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

if (!\function_exists('system_now')) {
    /**
     * Get current system time
     * @return DateTimeImmutable
     */
    function system_now(): DateTimeImmutable
    {
        return (new SystemClock())->now();
    }
}

if (!\function_exists('global_now')) {
    /**
     * Get global expected time
     * @return DateTimeImmutable
     * @throws \RuntimeException when global clock is not set
     */
    function global_now(): DateTimeImmutable
    {
        return GlobalClock::getGlobalClock()->now();
    }
}
