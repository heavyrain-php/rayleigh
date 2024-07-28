<?php

declare(strict_types=1);

/**
 * Class ClockTest
 * @package Rayleigh\Clock
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;
use RuntimeException;

/**
 * Represents customized global time
 * Suitable for request-scoped time or testing
 *
 * ```php
 * // You can fix request time in HTTP middleware
 * GlobalClock::setGlobalClock(new \DateTimeImmutable("now"));
 *
 * $response = $next($request);
 *
 * // Clear global time for the rest of process
 * GlobalClock::clearGlobalClock();
 *
 * return $response;
 * ```
 */
class GlobalClock implements ClockInterface
{
    use AsCarbonImmutableTrait;
    use AsChronosTrait;

    protected static ?GlobalClock $global_instance = null;

    private function __construct(
        private readonly ClockInterface $instance,
        private readonly ?DateTimeZone $timezone,
    ) {
        static::$global_instance = $this;
    }

    /**
     * Set global static-variable Clock
     * @param ClockInterface $instance    Clock instance(You can use SystemClock or \DateTimeImmutable or else)
     * @param DateTimeZone|null $timezone You can specify timezone
     * @param bool|null $force            Overwrite global clock
     * @return void
     */
    public static function setGlobalClock(ClockInterface $instance, ?DateTimeZone $timezone = null, ?bool $force = false): void
    {
        if ($force === false && static::$global_instance !== null) {
            throw new RuntimeException("Global clock is already set");
        }

        static::$global_instance = new GlobalClock($instance, $timezone);
    }

    /**
     * Get global static-variable Clock
     * @return GlobalClock
     * @throws RuntimeException when global clock is not set
     */
    public static function getGlobalClock(): GlobalClock
    {
        if (static::$global_instance === null) {
            throw new RuntimeException("Global clock is not set");
        }

        return static::$global_instance;
    }

    /**
     * Clear global static-variable Clock
     * @return void
     */
    public static function clearGlobalClock(): void
    {
        static::$global_instance = null;
    }

    /**
     * {@inheritDoc}
     */
    public function now(): DateTimeImmutable
    {
        $now = $this->instance->now();

        if ($this->timezone !== null) {
            return $now->setTimezone($this->timezone);
        }

        return $now;
    }
}
