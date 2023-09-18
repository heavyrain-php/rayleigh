<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Tests\Container;

final class CircularDepsA
{
    public function __construct(
        private readonly CircularDepsB $b,
    ) {
    }
}
