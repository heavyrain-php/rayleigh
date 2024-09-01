<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Console;

/**
 * Console InputInterface
 * @package Rayleigh\Console
 */
interface InputInterface
{
    /**
     * Get main command name to execute
     * @return null|string null when no name provided
     */
    public function getCommandName(): ?string;
}
