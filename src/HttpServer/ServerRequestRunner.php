<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 ServerRequest runner
 * @package Rayleigh\HttpServer
 * @link https://github.com/httpsoft/http-runner/
 * @link https://github.com/relayphp/Relay.Relay/
 * @link https://github.com/slimphp/Slim/blob/4.x/Slim/MiddlewareDispatcher.php
 * @example
 * ```php
 * return static function (ServerRequestInterface $request): ResponseInterface {
 *     $middlewares = []; // Add PSR-15 compatible middlewares
 *     return (new ServerRequestRunner($middlewares))->handle($request);
 * };
 */
class ServerRequestRunner implements RequestHandlerInterface
{
    /**
     * Constructor
     */
    public function __construct() {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new \RuntimeException('Not implemented');
    }
}
