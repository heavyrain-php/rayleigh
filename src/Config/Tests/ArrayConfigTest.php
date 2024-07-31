<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Config\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\Config\ArrayConfig;

#[CoversClass(ArrayConfig::class)]
final class ArrayConfigTest extends TestCase
{
    #[Test]
    public function testGetString(): void
    {
        $config = new ArrayConfig([
            'foo' => 'bar',
        ]);
        $this->assertSame('bar', $config->getString('foo'));
    }

    #[Test]
    public function testGetStringHasUndefinedKey(): void
    {
        $config = new ArrayConfig([
            'foo' => [],
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Undefined config key provided key=undefined');
        $config->getString('undefined');
    }

    #[Test]
    public function testGetStringHasInvalidValue(): void
    {
        $config = new ArrayConfig([
            'foo' => [],
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config value type provided key=foo value=array');
        $config->getString('foo');
    }

    #[Test]
    public function testGetStringArray(): void
    {
        $config = new ArrayConfig([
            'foo' => 'bar,,baz',
        ]);
        $this->assertSame(['bar', '', 'baz'], $config->getStringArray('foo'));
    }

    #[Test]
    public function testGetInteger(): void
    {
        $config = new ArrayConfig([
            'foo' => '123',
        ]);
        $this->assertSame(123, $config->getInteger('foo'));
    }

    #[Test]
    public function testGetIntegerHasInvalidValue(): void
    {
        $config = new ArrayConfig([
            'foo' => '0x0f',
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config value provided key=foo value=0x0f');
        $config->getInteger('foo');
    }

    #[Test]
    public function testGetIntegerArray(): void
    {
        $config = new ArrayConfig([
            'foo' => '123,456',
        ]);
        $this->assertSame([123, 456], $config->getIntegerArray('foo'));
    }

    #[Test]
    public function testGetIntegerArrayHasInvalidValue(): void
    {
        $config = new ArrayConfig([
            'foo' => '0,1,20000,0666,0x0aaf',
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config value provided key=foo value=0666');
        $config->getIntegerArray('foo');
    }

    #[Test]
    public function testGetBoolean(): void
    {
        $config = new ArrayConfig([
            'foo' => 'true',
            'bar' => 'false',
        ]);
        $this->assertSame(true, $config->getBoolean('foo'));
        $this->assertSame(false, $config->getBoolean('bar'));
    }

    #[Test]
    public function testGetBooleanHasInvalidValue(): void
    {
        $config = new ArrayConfig([
            'foo' => '?',
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid config value provided key=foo value=?');
        $config->getBoolean('foo');
    }
}
