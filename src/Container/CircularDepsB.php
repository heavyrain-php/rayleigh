<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Tests\Container;

final class CircularDepsB
{
    public function __construct(
        private readonly CircularDepsA $a,
    ) {
    }
}
