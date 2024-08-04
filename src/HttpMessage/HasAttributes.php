<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-7 Attributes trait
 * @package Rayleigh\HttpMessage
 */
trait HasAttributes
{
    /** @var array<array-key, mixed> $attributes */
    protected array $attributes = [];

    /**
     * Get attributes
     * @return array<array-key, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $attribute, mixed $default = null): mixed
    {
        return $this->attributes[$attribute] ?? $default;
    }

    public function withAttribute(string $attribute, mixed $value): ServerRequestInterface
    {
        $new_instance = clone $this;
        $new_instance->attributes[$attribute] = $value;

        return $new_instance;
    }

    public function withoutAttribute(string $attribute): ServerRequestInterface
    {
        $new_instance = clone $this;
        unset($new_instance->attributes[$attribute]);

        return $new_instance;
    }
}
