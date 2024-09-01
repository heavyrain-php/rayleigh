<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Console;

use Attribute;

/**
 * Console Option
 * @package Rayleigh\Console
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Option
{
    /**
     * Option constructor
     * @param string $flag e.g. -h
     * @param null|string $long e.g. --help
     * @param bool $required_value e.g. --name="John Doe"
     * @param null|string $default default value, default: null
     * @param string $description text description, default: ''
     */
    public function __construct(
        public string $flag,
        public ?string $long = null,
        public bool $required_value = false,
        public ?string $default = null,
        public string $description = '',
    ) {
    }
}
