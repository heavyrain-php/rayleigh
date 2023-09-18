<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Tests\Config;

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
    public function testGetStringArray(): void
    {
        $config = new ArrayConfig([
            'foo' => 'bar,baz',
        ]);
        $this->assertSame(['bar', 'baz'], $config->getStringArray('foo'));
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
    public function testGetIntegerArray(): void
    {
        $config = new ArrayConfig([
            'foo' => '123,456',
        ]);
        $this->assertSame([123, 456], $config->getIntegerArray('foo'));
    }

    #[Test]
    public function testGetBoolean(): void
    {
        $config = new ArrayConfig([
            'foo' => 'true',
        ]);
        $this->assertSame(true, $config->getBoolean('foo'));
    }
}
