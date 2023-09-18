<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Http;

use Amp\Http\HttpStatus;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler as RequestHandlerInterface;
use Amp\Http\Server\Response;

final class RequestHandler implements RequestHandlerInterface
{
    public function handleRequest(Request $request): Response
    {
        // TODO: main request handler
        return new Response(
            status: HttpStatus::OK,
            headers: ['Content-Type' => 'text/plain'],
            body: 'Hello, world!',
        );
    }
}
