<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Route information
 * @package Rayleigh\HttpServer
 * @psalm-suppress PossiblyUnusedProperty
 */
final /* readonly */ class Route
{
    /**
     * Constructor
     * @param string $method
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly RequestHandlerInterface $handler,
        public readonly array $middlewares = [],
    ) {}
}
