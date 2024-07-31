<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
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
