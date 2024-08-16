<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpFactory;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Rayleigh\HttpMessage\Request;

/**
 * PSR-17 Request factory implementation
 * @package Rayleigh\HttpFactory
 */
final /* readonly */ class RequestFactory implements RequestFactoryInterface
{
    /**
     * Create a new request
     * {@inheritDoc}
     * @param string $method
     * @param string|\Psr\Http\Message\UriInterface $uri
     * @return RequestInterface
     */
    public function createRequest(string $method, mixed $uri): RequestInterface
    {
        return new Request($method, $uri);
    }
}
