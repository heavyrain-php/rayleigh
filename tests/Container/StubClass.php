<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Tests\Container;

final class StubClass
{
    public function __construct(
        $a,
        float $b,
        int $c = 1,
        ...$d,
    ) {
    }
}
