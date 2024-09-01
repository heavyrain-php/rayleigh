<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rayleigh\HttpMessage\UriPlaceholderResolver;

/**
 * PSR-15 Routing handler
 * @package Rayleigh\HttpServer
 */
final /* readonly */ class RoutingHandler implements RequestHandlerInterface
{
    /** @var array<string, array<string, Route>> $routes */
    private array $routes = [];

    /**
     * Get routes
     * @return array<string, array<string, Route>>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Add GET route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function get(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Add POST route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function post(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Add PUT route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function put(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * Add DELETE route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function delete(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Add PATCH route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function patch(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }

    /**
     * Add HEAD route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function head(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('HEAD', $path, $handler, $middlewares);
    }

    /**
     * Add OPTIONS route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function options(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('OPTIONS', $path, $handler, $middlewares);
    }

    /**
     * Add TRACE route
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function trace(string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        $this->addRoute('TRACE', $path, $handler, $middlewares);
    }

    /**
     * Add route
     * @param string $method HTTP Method
     * @param string $path
     * @param RequestHandlerInterface $handler
     * @param array<int, MiddlewareInterface> $middlewares
     * @return void
     */
    public function addRoute(string $method, string $path, RequestHandlerInterface $handler, array $middlewares = []): void
    {
        if (\array_key_exists($path, $this->routes) === false) {
            $this->routes[$path] = [];
        }
        $this->routes[$path][$method] = new Route($method, $path, $handler, $middlewares);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = \strtoupper($request->getMethod());
        $path = $request->getUri()->getPath();

        // Check exact routes
        if (\array_key_exists($path, $this->routes) && \array_key_exists($method, $this->routes[$path])) {
            $route = $this->routes[$path][$method];
            return (new ServerRequestRunner($route->middlewares, $route->handler))->handle($request);
        }

        // Check placeholder routes
        $resolver = new UriPlaceholderResolver();
        foreach ($this->routes as $path_pattern => $routes) {
            if (\array_key_exists($method, $routes) === false) {
                continue;
            }
            $result = $resolver->resolve($request, $path_pattern);
            if ($result instanceof ServerRequestInterface === false) {
                continue;
            }
            $route = $routes[$method];
            return (new ServerRequestRunner($route->middlewares, $route->handler))->handle($result);
        }

        // Check wildcard routes
        if (\array_key_exists('*', $this->routes)) {
            $route = $this->routes['*'][$method] ?? $this->routes['*']['OPTIONS'] ?? null;
            if ($route !== null) {
                return (new ServerRequestRunner($route->middlewares, $route->handler))->handle($request);
            }
        }

        throw new RouteNotFoundException($method, $path);
    }
}
