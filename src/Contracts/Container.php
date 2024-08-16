<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Contracts;

use Psr\Container\ContainerInterface;

/**
 * Container interface
 * @package Rayleigh\Contracts
 */
interface Container extends ContainerInterface
{
    /**
     * {@inheritDoc}
     * @template T of object
     * @param class-string<T> $id
     * @return T
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get(string $id): mixed;

    /**
     * Bind a resolver to the container
     * @param string $id
     * @param mixed $resolver
     * @return void
     */
    public function bind(string $id, mixed $resolver): void;

    /**
     * Force bind a resolver to the container
     * @param string $id
     * @param mixed $resolver
     * @return void
     */
    public function forceBind(string $id, mixed $resolver): void;

    /**
     * Unbind a resolver from the container
     * @param string $id
     * @return void
     */
    public function unbind(string $id): void;

    /**
     * Check if the container has a resolver
     * @param callable $func
     * @param array<array-key, mixed> $args
     * @return mixed
     */
    public function call(callable $func, array $args = []): mixed;
}
