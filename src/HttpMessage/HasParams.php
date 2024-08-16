<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR-7 Params trait
 * @package Rayleigh\HttpMessage
 */
trait HasParams
{
    /** @var array<string, mixed> $server_params */
    protected array $server_params = [];

    /** @var array<string, mixed> $cookie_params */
    protected array $cookie_params = [];

    /** @var array<array-key, mixed> $query_params */
    protected array $query_params = [];

    /**
     * Get server params
     * @return array<string, mixed>
     */
    public function getServerParams(): array
    {
        return $this->server_params;
    }

    /**
     * Get cookie params
     * @return array<string, mixed>
     */
    public function getCookieParams(): array
    {
        return $this->cookie_params;
    }

    /**
     * With cookie params
     * @param array<array-key, mixed> $cookies
     * @return ServerRequestInterface
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new_instance = clone $this;
        $new_instance->cookie_params = $cookies;

        return $new_instance;
    }

    /**
     * Get query params
     * @return array<array-key, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->query_params;
    }

    /**
     * With query params
     * @param array<array-key, mixed> $query
     * @return ServerRequestInterface
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new_instance = clone $this;
        $new_instance->query_params = $query;

        return $new_instance;
    }
}
