<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container;

use Closure;
use Rayleigh\Contracts\Container as ContainerInterface;
use ReflectionFunction;
use RuntimeException;

/**
 * Represents Dependency Injection Container
 */
final class Container implements ContainerInterface
{
    /** @var array<string, mixed> $resolvers */
    private array $resolvers = [];
    private ClassResolver $class_resolver;

    public function __construct()
    {
        $this->class_resolver = new ClassResolver($this);
    }

    public function bind(string $id, mixed $resolver): void
    {
        if (\array_key_exists($id, $this->resolvers)) {
            throw new RuntimeException(\sprintf('%s is already registered to container', $id));
        }

        $this->forceBind($id, $resolver);
    }

    public function forceBind(string $id, mixed $resolver): void
    {
        if (\is_resource($resolver)) {
            throw new RuntimeException('resource cannot be registered to container to avoid memory leak');
        }

        $this->resolvers[$id] = $resolver;
    }

    public function unbind(string $id): void
    {
        unset($this->resolvers[$id]);
    }

    public function get(string $id): mixed
    {
        $resolver = $id;
        if ($this->has($id)) {
            $resolver = $this->resolvers[$id];
        }

        // the order of resolving is important!

        // 1. Concrete object
        if (\is_object($resolver)) {
            // returns the instanced object
            /**
             * @psalm-suppress InvalidReturnStatement
             * @phpstan-ignore-next-line
             */
            return $resolver;
        }

        // 2. Existing class
        if (\is_string($resolver) && \class_exists($resolver)) {
            // instances the class and returns it
            /**
             * @psalm-suppress InvalidReturnStatement
             * @phpstan-ignore-next-line
             */
            return $this->class_resolver->resolve($resolver);
        }

        // 3. Scalar
        if ($this->has($id)) {
            // returns the scalar or resource value
            /**
             * @phpstan-ignore-next-line
             */
            return $resolver;
        }

        throw new RuntimeException(\sprintf('%s is not registered to container', $id));
    }

    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->resolvers);
    }

    public function call(callable $func, array $args = []): mixed
    {
        $ref = new ReflectionFunction(Closure::fromCallable($func));

        if ($ref->getNumberOfParameters() === 0) {
            // no parameters
            return $ref->invoke();
        }

        $resolvedArgs = [];
        foreach ($ref->getParameters() as $param) {
            if (\array_key_exists($param->getName(), $args)) {
                // the argument is explicitly passed
                $resolvedArgs[] = $args[$param->getName()];
                continue;
            }
            $resolvedArgs[$param->getName()] = $this->class_resolver->resolveParameter($param);
        }

        return $ref->invokeArgs($resolvedArgs);
    }
}
