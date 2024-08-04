<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Tests;

use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\Log\Rfc5424LogLevel;
use Rayleigh\Log\Writers\MonologWriter;

/**
 * Class MonologWriterTest
 * @package Rayleigh\Log\Tests\Writers
 */
#[CoversClass(MonologWriter::class)]
final class MonologWriterTest extends TestCase
{
    #[Test]
    public function testMonologWriter(): void
    {
        $monolog = $this->createMock(\Monolog\Logger::class);

        $writer = new MonologWriter($monolog);

        $get_monolog = $writer->getMonologLogger();

        self::assertSame($monolog, $get_monolog);

        $monolog->expects($this->any())
            ->method('log');
        // TODO: Fix this
        // ->with(Level::Emergency, 'emergency', ['context3' => 'context3']);

        $writer->write(Rfc5424LogLevel::EMERGENCY, 'emergency', ['context3' => 'context3']);
        $writer->write(Rfc5424LogLevel::ALERT, 'alert', ['context2' => 'context2']);
        $writer->write(Rfc5424LogLevel::CRITICAL, 'critical', ['context' => 'context']);
        $writer->write(Rfc5424LogLevel::ERROR, 'error', ['context' => 'context']);
        $writer->write(Rfc5424LogLevel::WARNING, 'warning', ['context' => 'context']);
        $writer->write(Rfc5424LogLevel::NOTICE, 'notice', ['context' => 'context']);
        $writer->write(Rfc5424LogLevel::INFO, 'info', ['context' => 'context']);
        $writer->write(Rfc5424LogLevel::DEBUG, 'debug', ['context' => 'context']);
    }
}
