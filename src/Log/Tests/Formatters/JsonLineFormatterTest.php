<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Tests\Formatters;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\Log\Formatters\JsonLineFormatter;
use Rayleigh\Log\Rfc5424LogLevel;

/**
 * Class JsonLineFormatterTest
 * @package Rayleigh\Log\Tests\Formatters
 */
#[CoversClass(JsonLineFormatter::class)]
#[CoversClass(Rfc5424LogLevel::class)]
final class JsonLineFormatterTest extends TestCase
{
    #[Test]
    public function testFormat(): void
    {
        $formatter = new JsonLineFormatter();

        $formatted = $formatter->format(Rfc5424LogLevel::ERROR, 'test', ['a' => 'b']);

        self::assertSame('{"level":"error","message":"test","context":{"a":"b"}}' . PHP_EOL, $formatted);
    }

    #[Test]
    public function testFormatError(): void
    {
        $formatter = new JsonLineFormatter();

        $resource = \fopen('php://memory', 'r');
        $formatted = $formatter->format(Rfc5424LogLevel::ERROR, 'test', ['a' => $resource]);

        self::assertSame('Type is not supported' . PHP_EOL, $formatted);
    }
}
