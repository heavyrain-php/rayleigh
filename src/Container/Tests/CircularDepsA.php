<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container\Tests;

final class CircularDepsA
{
    public function __construct(
        private readonly CircularDepsB $b,
    ) {
    }
}
