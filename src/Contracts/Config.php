<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Contracts;

interface Config
{
    public function getString(string $key): string;
    public function getStringArray(string $key): array;
    public function getInteger(string $key): int;
    public function getIntegerArray(string $key): array;
    public function getBoolean(string $key): bool;
}
