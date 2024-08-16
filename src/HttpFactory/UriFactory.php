<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpFactory;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Rayleigh\HttpMessage\Uri;

/**
 * PSR-17 Uri factory implementation
 * @package Rayleigh\HttpFactory
 */
final /* readonly */ class UriFactory implements UriFactoryInterface
{
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
