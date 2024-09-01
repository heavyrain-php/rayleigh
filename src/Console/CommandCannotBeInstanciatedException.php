<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Console;

use RuntimeException;

/**
 * Console CommandCannotBeInstanciatedException
 * @package Rayleigh\Console
 */
final class CommandCannotBeInstanciatedException extends RuntimeException
{
    public function __construct(string $commandName)
    {
        parent::__construct("Command {$commandName} cannot be instanciated");
    }
}
