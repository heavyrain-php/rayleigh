<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Contracts;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function bind(string $id, mixed $resolver): void;
    public function forceBind(string $id, mixed $resolver): void;
    public function unbind(string $id): void;
    public function call(callable $func, array $args = []): mixed;
}
