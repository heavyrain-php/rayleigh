<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Rayleigh\Log\Rfc5424LogLevel;

/**
 * Class Rfc5424LogLevelTest
 * @package Rayleigh\Log\Tests
 */
#[CoversClass(Rfc5424LogLevel::class)]
final class Rfc5424LogLevelTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: int, 2: Rfc5424LogLevel, 3: string}>
     */
    public static function getMap(): array
    {
        return [
            'emergency' => ['emergency', 0, Rfc5424LogLevel::EMERGENCY, LogLevel::EMERGENCY],
            'alert' => ['alert', 1, Rfc5424LogLevel::ALERT, LogLevel::ALERT],
            'critical' => ['critical', 2, Rfc5424LogLevel::CRITICAL, LogLevel::CRITICAL],
            'error' => ['error', 3, Rfc5424LogLevel::ERROR, LogLevel::ERROR],
            'warning' => ['warning', 4, Rfc5424LogLevel::WARNING, LogLevel::WARNING],
            'notice' => ['notice', 5, Rfc5424LogLevel::NOTICE, LogLevel::NOTICE],
            'info' => ['info', 6, Rfc5424LogLevel::INFO, LogLevel::INFO],
            'debug' => ['debug', 7, Rfc5424LogLevel::DEBUG, LogLevel::DEBUG],
        ];
    }

    #[Test]
    #[DataProvider('getMap')]
    public function testFromTo(string $str, int $syslogLevel, Rfc5424LogLevel $expected, string $psr): void
    {
        self::assertSame($expected, Rfc5424LogLevel::fromMixed($str));
        self::assertSame($expected, Rfc5424LogLevel::fromMixed($syslogLevel));
        self::assertSame($syslogLevel, $expected->value);
        self::assertSame($str, $expected->toPsrLogLevel());
    }
}
