<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpFactory;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rayleigh\HttpMessage\ServerRequest;

/**
 * PSR-17 ServerRequest factory implementation
 * @package Rayleigh\HttpFactory
 */
final /* readonly */ class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritDoc}
     * @param string $method
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param array<string, scalar> $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }
}
