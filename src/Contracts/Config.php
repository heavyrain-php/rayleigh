<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Contracts;

/**
 * Config interface
 * @package Rayleigh\Contracts
 */
interface Config
{
    /**
     * Get string value from config
     * @param string $key
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getString(string $key): string;

    /**
     * Get string array value from config
     * @param string $key
     * @return string[]
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getStringArray(string $key): array;

    /**
     * Get integer value from config
     * @param string $key
     * @return int
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getInteger(string $key): int;

    /**
     * Get integer array value from config
     * @param string $key
     * @return int[]
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getIntegerArray(string $key): array;

    /**
     * Get boolean value from config
     * @param string $key
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function getBoolean(string $key): bool;
}
