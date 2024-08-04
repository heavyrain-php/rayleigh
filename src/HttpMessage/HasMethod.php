<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\RequestInterface;

/**
 * PSR-7 Method trait
 * @package Rayleigh\HttpMessage
 */
trait HasMethod
{
    protected string $method = 'GET';

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): RequestInterface
    {
        $new_instance = clone $this;
        $new_instance->method = $method;

        return $new_instance;
    }
}
