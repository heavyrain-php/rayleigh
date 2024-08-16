<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpFactory;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Rayleigh\HttpMessage\Response;

/**
 * PSR-17 Response factory implementation
 * @package Rayleigh\HttpFactory
 */
final /* readonly */ class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if ($reasonPhrase === '') {
            $reasonPhrase = null;
        }
        return new Response($code, [], null, '1.1', $reasonPhrase);
    }
}
