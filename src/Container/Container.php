<?php

declare(strict_types=1);

/**
 * @license MIT
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
    private array $resolvers = [];
    private ClassResolver $classResolver;

    public function __construct()
    {
        $this->classResolver = new ClassResolver($this);
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

    public function get(string $id)
    {
        $resolver = $id;
        if ($this->has($id)) {
            $resolver = $this->resolvers[$id];
        }

        // the order of resolving is important!

        // 1. Concrete object
        if (\is_object($resolver)) {
            // returns the instanced object
            return $resolver;
        }

        // 2. Existing class
        if (\is_string($resolver) && \class_exists($resolver)) {
            // instances the class and returns it
            return $this->classResolver->resolve($id, $resolver);
        }

        // 3. Scalar
        if ($this->has($id)) {
            // returns the scalar or resource value
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
            $resolvedArgs[$param->getName()] = $this->classResolver->resolveParameter($param);
        }

        return $ref->invokeArgs($resolvedArgs);
    }
}
