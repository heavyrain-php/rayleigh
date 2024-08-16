<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * Class resolver from class-name
 * @psalm-internal
 * @internal
 */
final class ClassResolver
{
    /**
     * @var array<class-string, bool>
     */
    private array $resolving_concretes = [];

    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    /**
     * Resolves a class name to an object.
     *
     * @template T of object
     * @param string $resolver
     * @phpstan-param class-string<T> $resolver
     * @return object
     * @phpstan-return T
     * @throws ReflectionException
     */
    public function resolve(string $resolver): object
    {
        $ref = new ReflectionClass($resolver);

        if (!$ref->isInstantiable()) {
            throw new ReflectionException(\sprintf('%s is not instantiable', $resolver)); // @codeCoverageIgnore
        }

        if ($ref->getConstructor() === null || $ref->getConstructor()->getNumberOfParameters() === 0) {
            // no constructor or no parameters
            return $ref->newInstance();
        }

        if (\array_key_exists($resolver, $this->resolving_concretes)) {
            throw new ReflectionException(\sprintf('circular dependency detected: %s', $resolver));
        }
        $this->resolving_concretes[$resolver] = true;

        $resolvedParams = [];
        foreach ($ref->getConstructor()->getParameters() as $param) {
            $resolvedParams[] = $this->resolveParameter($param);
        }

        unset($this->resolving_concretes[$resolver]);
        $concrete = $ref->newInstanceArgs($resolvedParams);

        // @phpstan-ignore identical.alwaysFalse
        if ($concrete === null) {
            throw new ReflectionException(\sprintf('failed to instantiate %s', $resolver)); // @codeCoverageIgnore
        }

        return $concrete;
    }

    /**
     * Resolves ReflectionParameter to a value.
     *
     * @param ReflectionParameter $param
     * @return mixed
     */
    public function resolveParameter(ReflectionParameter $param): mixed
    {
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        $name = $param->getName();

        if ($param->isVariadic()) {
            throw new ReflectionException(\sprintf('variadic parameter %s is not supported', $name));
        }

        if (!$param->hasType()) {
            throw new ReflectionException(\sprintf('type of "%s" is not defined. All constructor properties must have type.', $name));
        }

        $type = $param->getType();
        \assert(!\is_null($type));
        \assert($type instanceof \ReflectionNamedType);
        $typeName = $type->getName();

        if ($type->isBuiltin() && !$this->container->has($typeName)) {
            throw new ReflectionException(\sprintf('built-in parameter type %s is not supported', $typeName));
        }
        return $this->container->get($typeName);
    }
}
