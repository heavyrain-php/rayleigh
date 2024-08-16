<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rayleigh\Container\ClassResolver;
use Rayleigh\Container\Container;

#[UsesClass(ClassResolver::class)]
#[CoversClass(Container::class)]
final class ContainerTest extends TestCase
{
    /** @var resource|null $resource */
    private $resource = null;

    protected function tearDown(): void
    {
        if ($this->resource !== null) {
            \fclose($this->resource);
            $this->resource = null;
        }
    }

    #[Test]
    public function testCannotRebind(): void
    {
        $container = new Container();

        $container->bind('foo', 'bar');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('foo is already registered to container');
        $container->bind('foo', 'baz');
    }

    #[Test]
    public function testCannotBindResource(): void
    {
        $container = new Container();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('resource cannot be registered to container to avoid memory leak');
        $resource = \fopen(__FILE__, 'r');
        \assert($resource !== false);
        $this->resource = $resource;
        $container->bind('foo', $this->resource);
    }

    #[Test]
    public function testUnbind(): void
    {
        $container = new Container();

        $container->bind('foo', 'bar');
        $container->unbind('foo');
        $container->unbind('foo'); // no error when unbinding non-existing id

        // for not treated as risky test
        self::assertTrue(true);
    }

    #[Test]
    public function testGet(): void
    {
        $container = new Container();

        // Concrete object
        $container->bind('concrete object', $a = new \stdClass());
        /** @phpstan-ignore-next-line */
        self::assertSame($a, $container->get('concrete object'));
        self::assertTrue($container->has('concrete object'));
        self::assertFalse($container->has('non-existing id'));

        // Callable instance
        $container->bind('callable instance', new class {
            public function __invoke(): string
            {
                return 'world';
            }
        });
        /** @var callable $callable */
        /** @phpstan-ignore-next-line */
        $callable = $container->get('callable instance');
        self::assertSame('world', $callable());

        // Callable
        $container->bind('callable', fn() => 'hello');
        /** @var callable $callable2 */
        /** @phpstan-ignore-next-line */
        $callable2 = $container->get('callable');
        self::assertSame('hello', $callable2());

        // Existing class
        $container->bind(\stdClass::class, $a = new \stdClass());
        $instance = $container->get(\stdClass::class);
        self::assertSame($a, $instance);

        // Existing class with constructor
        $container->bind(Container::class, $container);
        $instance = $container->get(Container::class);
        self::assertSame($container, $instance);

        // Existing class with constructor and parameters
        $container->bind(ContainerInterface::class, $container);
        // $container->bind(ClassResolver::class, ClassResolver::class);
        $instance = $container->get(ClassResolver::class);
        self::assertInstanceOf(ClassResolver::class, $instance);

        // Scalar
        $container->bind('scalar', 4);
        /** @phpstan-ignore-next-line */
        $scalar = $container->get('scalar');
        self::assertSame(4, $scalar);
    }

    #[Test]
    public function testGetFailed(): void
    {
        $container = new Container();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('id is not registered to container');
        /** @phpstan-ignore-next-line */
        $container->get('id');
    }

    #[Test]
    public function testCall(): void
    {
        $container = new Container();

        self::assertSame('hello', $container->call(fn() => 'hello'));

        $container->bind(ContainerInterface::class, $container);

        self::assertFalse($container->call(fn(ContainerInterface $container): bool => $container->has('id')));

        $container2 = new Container();

        // @phpstan-ignore return.type
        self::assertSame($container2, $container->call(fn(ContainerInterface $container2): Container => $container2, compact('container2')));
    }
}
