<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log;

/**
 * Format log message
 * @package Rayleigh\Log
 */
interface FormatterInterface
{
    /**
     * Format log message
     * @param Rfc5424LogLevel $log_level log level
     * @param string $message Message line
     * @param array<array-key, mixed> $context Message context
     * @return string formatted message
     */
    function format(Rfc5424LogLevel $log_level, string $message, array $context): string;
}
