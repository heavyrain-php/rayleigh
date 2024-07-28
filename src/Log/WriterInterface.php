<?php

declare(strict_types=1);

/**
 * Class Logger
 * @package Rayleigh\Log
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log;

/**
 * Write log message to anywhere
 */
interface WriterInterface
{
    /**
     * Write log message
     * @param Rfc5424LogLevel $log_level log level
     * @param string $message Message line
     * @param array<array-key, mixed> $context Message context
     * @return void
     */
    function write(Rfc5424LogLevel $log_level, string $message, array $context): void;
}
