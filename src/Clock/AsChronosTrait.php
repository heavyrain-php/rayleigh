<?php

declare(strict_types=1);

/**
 * Class AsChronosTrait
 * @package Rayleigh\Clock
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Clock;

use Cake\Chronos\Chronos;
use RuntimeException;

/**
 * Get time as Chronos\Chronos
 */
trait AsChronosTrait
{
    /**
     * Get current time as Chronos
     * @return Chronos
     * @throws RuntimeException when Chronos is not installed
     */
    public function asChronos(): Chronos
    {
        if (!\class_exists(Chronos::class)) {
            throw new RuntimeException("You need to install Chronos with `$ composer require cakephp/chronos`"); // @codeCoverageIgnore
        }

        return new Chronos($this->now());
    }
}
