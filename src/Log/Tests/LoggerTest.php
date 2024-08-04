<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Tests;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\Log\Logger;
use Rayleigh\Log\Rfc5424LogLevel;
use Rayleigh\Log\WriterInterface;

/**
 * Class LoggerTest
 * @package Rayleigh\Log\Tests
 */
#[CoversClass(Logger::class)]
#[CoversClass(Rfc5424LogLevel::class)]
final class LoggerTest extends TestCase
{
    #[After]
    protected function tearDown(): void
    {
        Logger::clearInstance();
    }

    #[Test]
    public function testLogging(): void
    {
        $writer = $this->createMock(WriterInterface::class);

        $logger = new Logger([$writer]);

        Logger::setInstance($logger);

        $logger_instance = Logger::getInstance();

        self::assertSame($logger, $logger_instance);

        $writer->expects($this->any())
            ->method('write');
        // TODO: Fix this
        // ->with($this->equalTo(Rfc5424LogLevel::EMERGENCY), $this->equalTo('emergency'), $this->equalTo(['context3' => 'context3']));

        $logger->emergency('emergency', ['context3' => 'context3']);

        $logger->alert('alert', ['context2' => 'context2']);

        $logger->critical('critical', ['context' => 'context']);

        $logger->error('error', ['context' => 'context']);

        $logger->warning('warning', ['context' => 'context']);

        $logger->notice('notice', ['context' => 'context']);

        $logger->info('info', ['context' => 'context']);

        $logger->debug('debug', ['context' => 'context']);

        Logger::clearInstance();
    }

    #[Test]
    public function testLoggingWithoutWriters(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No writers are set');

        new Logger([]);
    }

    #[Test]
    public function testSetInstance(): void
    {
        $logger = new Logger([self::createStub(WriterInterface::class)]);

        Logger::setInstance($logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Logger instance is already set');

        Logger::setInstance($logger);
    }

    #[Test]
    public function testGetInstance(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Logger instance is not set');

        Logger::getInstance();
    }

    #[Test]
    public function testInvalidLogLevel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid log level: ');

        Rfc5424LogLevel::fromMixed(false);
    }
}
