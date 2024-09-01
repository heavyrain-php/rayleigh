<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Console\Output;

use Rayleigh\Console\OutputInterface;

/**
 * Console ArrayOutput
 * @package Rayleigh\Console
 */
final class ArrayOutput implements OutputInterface
{
    /** @var string[] $lines */
    private array $lines = [];

    public function writeLine(string $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * Get outputted lines
     * @return string[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }
}
