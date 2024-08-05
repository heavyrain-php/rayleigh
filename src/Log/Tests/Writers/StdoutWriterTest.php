<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Tests\Writers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\Log\FormatterInterface;
use Rayleigh\Log\Writers\StdoutWriter;

/**
 * Class StdoutWriterTest
 * @package Rayleigh\Log\Tests\Writers
 */
#[CoversClass(StdoutWriter::class)]
final class StdoutWriterTest extends TestCase
{
    #[Test]
    public function testConstructor(): void
    {
        $formatter = self::createStub(FormatterInterface::class);
        $writer = new StdoutWriter($formatter);
        self::assertInstanceOf(StdoutWriter::class, $writer);
    }
}
