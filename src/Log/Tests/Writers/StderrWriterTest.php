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
use Rayleigh\Log\Writers\StderrWriter;

/**
 * Class StderrWriterTest
 * @package Rayleigh\Log\Tests\Writers
 */
#[CoversClass(StderrWriter::class)]
final class StderrWriterTest extends TestCase
{
    #[Test]
    public function testConstructor(): void
    {
        $formatter = self::createStub(FormatterInterface::class);
        $writer = new StderrWriter($formatter);
        self::assertInstanceOf(StderrWriter::class, $writer);
    }
}
