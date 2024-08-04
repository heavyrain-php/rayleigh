<?php

declare(strict_types=1);

/**
 * Class Logger
 * @package Rayleigh\Log
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * PSR-3 compatible Logger
 */
/* final readonly */ class Logger extends AbstractLogger
{
    /** singleton instance */
    protected static ?Logger $global_instance = null;

    /**
     * @param WriterInterface[] $writers
     */
    public function __construct(
        protected readonly array $writers,
    ) {
        if (count($this->writers) === 0) {
            throw new \RuntimeException("No writers are set");
        }
    }

    /**
     * Set singleton instance
     * @param Logger $instance
     * @return void
     * @throws \RuntimeException when instance is already set
     */
    public static function setInstance(Logger $instance): void
    {
        if (self::$global_instance !== null) {
            throw new \RuntimeException("Logger instance is already set");
        }
        self::$global_instance = $instance;
    }

    /**
     * Get singleton instance
     * @return static
     * @throws \RuntimeException when instance is not set
     *
     * ```php
     * Logger::getInstance()->info("Message", compact('context'));
     * ```
     */
    public static function getInstance(): self
    {
        if (self::$global_instance === null) {
            throw new \RuntimeException("Logger instance is not set");
        }

        // @phpstan-ignore return.type
        return self::$global_instance;
    }

    /**
     * Clear singleton instance
     * @return void
     */
    public static function clearInstance(): void
    {
        self::$global_instance = null;
    }

    /**
     * {@inheritDoc}
     * @param array<array-key, mixed> $context
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        foreach ($this->writers as $writer) {
            $writer->write(Rfc5424LogLevel::fromMixed($level), (string)$message, $context);
        }
    }
}
