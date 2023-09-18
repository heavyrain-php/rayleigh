<?php declare(strict_types=1);

/**
 * @license MIT
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
