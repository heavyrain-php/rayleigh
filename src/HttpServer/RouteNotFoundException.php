<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer;

use RuntimeException;

/**
 * Route not found exception
 * @package Rayleigh\HttpServer
 */
class RouteNotFoundException extends RuntimeException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct("Route not found: {$method} {$path}", 404);
    }
}
