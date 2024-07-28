<?php

declare(strict_types=1);

/**
 * Class Logger
 * @package Rayleigh\Log
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log;

use Psr\Log\LogLevel;

/**
 * RFC 5424 Syslog Message Severities
 * @see https://datatracker.ietf.org/doc/html/rfc5424#section-6.2.1
 * @see https://github.com/php-fig/log/blob/master/src/LogLevel.php
 * @see https://github.com/Seldaek/monolog/blob/main/src/Monolog/Level.php
 */
enum Rfc5424LogLevel: int
{
    /** Emergency: system is unusable */
    case EMERGENCY = 0;
    /** Alert: action must be taken immediately */
    case ALERT = 1;
    /** Critical: critical conditions */
    case CRITICAL = 2;
    /** Error: error conditions */
    case ERROR = 3;
    /** Warning: warning conditions */
    case WARNING = 4;
    /** Notice: normal but significant condition */
    case NOTICE = 5;
    /** Informational: informational messages */
    case INFO = 6;
    /** Debug: debug-level messages */
    case DEBUG = 7;

    /**
     * Get RFC 5424 log level from mixed
     * @param mixed $level
     * @return Rfc5424LogLevel
     */
    public static function fromMixed(mixed $level): self
    {
        if (\is_int($level)) {
            return self::from($level);
        }
        if (\is_string($level)) {
            return self::fromString($level);
        }
        throw new \InvalidArgumentException("Invalid log level: " . \print_r($level, true));
    }

    /**
     * Get RFC 5424 log level from string
     * @param string $name
     * @return Rfc5424LogLevel
     * @throws \InvalidArgumentException when log level is not defined
     */
    public static function fromString(string $name): self
    {
        $lower_name = \strtolower($name);

        return match ($lower_name) {
            'emergency' => self::EMERGENCY,
            'alert' => self::ALERT,
            'critical' => self::CRITICAL,
            'error' => self::ERROR,
            'warning' => self::WARNING,
            'notice' => self::NOTICE,
            'info' => self::INFO,
            'debug' => self::DEBUG,
            default => throw new \InvalidArgumentException("Undefined log level: {$name}"),
        };
    }

    /**
     * Convert RFC 5424 log level to PSR-3 log level
     * @return string
     */
    public function toPsrLogLevel(): string
    {
        return match ($this) {
            self::EMERGENCY => LogLevel::EMERGENCY,
            self::ALERT => LogLevel::ALERT,
            self::CRITICAL => LogLevel::CRITICAL,
            self::ERROR => LogLevel::ERROR,
            self::WARNING => LogLevel::WARNING,
            self::NOTICE => LogLevel::NOTICE,
            self::INFO => LogLevel::INFO,
            self::DEBUG => LogLevel::DEBUG,
        };
    }
}
