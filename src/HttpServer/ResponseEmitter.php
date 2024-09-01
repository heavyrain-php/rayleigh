<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * PSR-7 Response Emitter for traditional web server
 * @package Rayleigh\HttpServer
 */
final /* readonly */ class ResponseEmitter
{
    /**
     * Constructor
     * @param Emitter $emitter
     */
    public function __construct(
        private readonly Emitter $emitter,
    ) {}

    /**
     * Emit header and body
     * @param ResponseInterface $response
     * @return void
     * @throws RuntimeException
     */
    public function emit(ResponseInterface $response): void
    {
        $this->validateToEmit();
        $this->emitHeader($response);
        $this->emitter->emitBody($response->getBody());
    }

    /**
     * Terminate http request
     * @return void
     * @throws RuntimeException
     */
    public function terminate(): void
    {
        $this->emitter->terminateResponse();
    }

    /**
     * Validate to emit
     * @return void
     * @throws RuntimeException
     */
    private function validateToEmit(): void
    {
        if ($this->emitter->hasSentHeader()) {
            throw new RuntimeException('Header has already been sent');
        }

        if ($this->emitter->hasObFlushed()) {
            throw new RuntimeException('Output buffer has already been flushed');
        }
    }

    private function emitHeader(ResponseInterface $response): void
    {
        $status_code = $response->getStatusCode();
        $informational_response = $status_code >= 100 && $status_code < 200;
        if ($informational_response && \function_exists('headers_sent') === false) {
            // Skip when SAPI does not support headers_sent
            return; // @codeCoverageIgnore
        }

        /** @var string $name Fixes PSR-7 definition */
        foreach ($response->getHeaders() as $name => $value) {
            $name = \ucwords($name, '-');
            $replace = $name !== 'Set-Cookie'; // Do not replace only Set-Cookie
            foreach ($value as $v) {
                $this->emitter->emitHeader(
                    $name,
                    $v,
                    $replace,
                    $status_code,
                );
            }
        }

        $this->emitter->emitStatusLine(
            $response->getProtocolVersion(),
            $status_code,
            $response->getReasonPhrase(),
        );
    }
}
