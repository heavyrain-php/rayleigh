<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container\Tests;

final class StubClass
{
    // @phpstan-ignore-next-line
    public function __construct(
        $a,
        float $b,
        int $c = 1,
        ...$d,
    ) {
    }
}
