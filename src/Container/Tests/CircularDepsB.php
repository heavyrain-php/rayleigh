<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container\Tests;

final class CircularDepsB
{
    public function __construct(
        // @phpstan-ignore property.onlyWritten
        private readonly CircularDepsA $a,
    ) {
    }
}
