<?php

declare(strict_types=1);

/**
 * Class Logger
 * @package Rayleigh\Log\Writers
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Writers;

use Rayleigh\Log\Rfc5424LogLevel;
use Rayleigh\Log\WriterInterface;
use Monolog\Level;
use Monolog\Logger;

/**
 * Use monolog/monolog as writer
 * @see https://github.com/Seldaek/monolog/
 *
 * ```php
 * $logger = new \Monolog\Logger('appname');
 * $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout'));
 * $writer = new \Rayleigh\Log\Writers\MonologWriter($logger);
 * $logger = new \Rayleigh\Log\Logger([$writer]);
 * $logger->info('Hello');
 * ```
 */
class MonologWriter implements WriterInterface
{
    public function __construct(private readonly Logger $logger)
    {
    }

    /**
     * Get Monolog instance
     * @return Logger
     */
    public function getMonologLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * {@inheritDoc}
     */
    public function write(Rfc5424LogLevel $log_level, string $message, array $context): void
    {
        $this->logger->log($this->convertLogLevel($log_level), $message, $context);
    }

    private function convertLogLevel(Rfc5424LogLevel $log_level): Level
    {
        return match ($log_level) {
            Rfc5424LogLevel::EMERGENCY => Level::Emergency,
            Rfc5424LogLevel::ALERT => Level::Alert,
            Rfc5424LogLevel::CRITICAL => Level::Critical,
            Rfc5424LogLevel::ERROR => Level::Error,
            Rfc5424LogLevel::WARNING => Level::Warning,
            Rfc5424LogLevel::NOTICE => Level::Notice,
            Rfc5424LogLevel::INFO => Level::Info,
            Rfc5424LogLevel::DEBUG => Level::Debug,
        };
    }
}
