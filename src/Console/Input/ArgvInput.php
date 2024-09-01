<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Console\Input;

use Rayleigh\Console\InputInterface;

/**
 * Console ArgvInput
 * @package Rayleigh\Console
 * example:
 * ```php
 * $input = new ArgvInput();
 * ```
 */
final class ArgvInput implements InputInterface
{
    /** @var array<array-key, scalar> $input */
    public readonly array $input;

    /**
     * ArrayInput constructor
     * @param null|array<array-key, scalar> $input
     */
    public function __construct(
        ?array $input = null,
    ) {
        $this->input = $input ?? $_SERVER['argv'] ?? [];
    }

    public function getCommandName(): ?string
    {
        foreach ($this->input as $key => $value) {
            if (\is_int($key) && \is_string($value) && !\str_starts_with($value, '-')) {
                return $value;
            }
        }
        return null;
    }
}
