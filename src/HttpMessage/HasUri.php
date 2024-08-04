<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 Uri trait
 * @package Rayleigh\HttpMessage
 */
trait HasUri
{
    protected UriInterface $uri;

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        $new_instance = clone $this;
        $new_instance->uri = $uri;

        if (!$preserveHost || $this->hasHeader('Host') === false) {
            $new_instance->header_bag->updateHostHeaderFromUri($uri, true);
        }

        return $new_instance;
    }
}
