<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Tests\Container;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Rayleigh\Container\ClassResolver;
use Rayleigh\Container\Container;
use ReflectionException;

#[UsesClass(Container::class)]
#[CoversClass(ClassResolver::class)]
final class ClassResolverTest extends TestCase
{
    #[Test]
    public function testResolveNotExist(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $resolver = new ClassResolver($container);

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class "Rayleigh\Tests\Container\ClassResolverTesta" does not exist');

        $resolver->resolve('Rayleigh\Tests\Container\ClassResolverTesta');
    }

    #[Test]
    public function testResolveEmptyConstructor(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $resolver = new ClassResolver($container);

        $stdClass = $resolver->resolve('\stdClass');
        self::assertInstanceOf(\stdClass::class, $stdClass);
    }

    #[Test]
    public function testResolveCircularDependency(): void
    {
        $container = new Container();
        $resolver = new ClassResolver($container);

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('circular dependency detected: Rayleigh\Tests\Container\CircularDepsB');

        $resolver->resolve(CircularDepsA::class);
    }

    #[Test]
    public function testResolve(): void
    {
        $container = new Container();
        $container->bind(ContainerInterface::class, $container);
        $resolver = new ClassResolver($container);

        $actual = $resolver->resolve(ClassResolver::class);

        self::assertInstanceOf(ClassResolver::class, $actual);
    }

    #[Test]
    public function testResolveParameter(): void
    {
        $container = new Container();
        $container->bind(ContainerInterface::class, $container);
        $resolver = new ClassResolver($container);

        $actual = $resolver->resolveParameter(new \ReflectionParameter([ClassResolver::class, '__construct'], 'container'));

        self::assertSame($container, $actual);
    }

    #[Test]
    public function testResolveParameterDefaultValue(): void
    {
        $container = new Container();
        $resolver = new ClassResolver($container);

        $actual = $resolver->resolveParameter(new \ReflectionParameter([StubClass::class, '__construct'], 'c'));

        self::assertSame(1, $actual);
    }

    #[Test]
    public function testResolveParameterIsVariadic(): void
    {
        $container = new Container();
        $resolver = new ClassResolver($container);

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('variadic parameter d is not supported');

        $resolver->resolveParameter(new \ReflectionParameter([StubClass::class, '__construct'], 'd'));
    }

    #[Test]
    public function testResolveParameterNotHasType(): void
    {
        $container = new Container();
        $resolver = new ClassResolver($container);

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('type of "a" is not defined. All constructor properties must have type.');

        $resolver->resolveParameter(new \ReflectionParameter([StubClass::class, '__construct'], 'a'));
    }

    #[Test]
    public function testResolveParameterBuiltIn(): void
    {
        $container = new Container();
        $resolver = new ClassResolver($container);

        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('built-in parameter type float is not supported');

        $resolver->resolveParameter(new \ReflectionParameter([StubClass::class, '__construct'], 'b'));
    }
}
