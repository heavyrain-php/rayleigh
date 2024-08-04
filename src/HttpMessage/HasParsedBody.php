<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-7 ParsedBody trait
 * @package Rayleigh\HttpMessage
 */
trait HasParsedBody
{
    /** @var array<array-key, mixed>|object|null */
    protected array|object|null $parsed_body = null;

    /**
     * Get parsed body
     * @return array<array-key, mixed>|object|null
     */
    public function getParsedBody(): array|object|null
    {
        return $this->parsed_body;
    }

    /**
     * With parsed body
     * @param mixed $parsed_body
     * @return ServerRequestInterface
     */
    public function withParsedBody(mixed $parsed_body): ServerRequestInterface
    {
        $new_instance = clone $this;

        if (!\is_array($parsed_body) && !\is_object($parsed_body) && $parsed_body !== null) {
            throw new \InvalidArgumentException('Invalid parsed body');
        }
        $new_instance->parsed_body = $parsed_body;

        return $new_instance;
    }
}
