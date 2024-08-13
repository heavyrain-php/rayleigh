<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 * @codeCoverageIgnoreFile because all of methods are wrapper of PHP built-in functions
 */

namespace Rayleigh\HttpServer;

use Stringable;

/**
 * Header and body emitter function wrapper
 * @package Rayleigh\HttpServer
 */
/* final readonly */ class Emitter
{
    /**
     * Terminate response
     * @return void
     */
    public function terminateResponse(): void
    {
        if (!$this->hasSentHeader()) {
            throw new \RuntimeException('Header has not been sent yet');
        }

        if (\function_exists('fastcgi_finish_request')) {
            // fastcgi
            \fastcgi_finish_request();
        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            // cli
            \ob_end_flush();
            \flush();
        }
    }

    /**
     * Header has sent or not
     * @return bool
     */
    public function hasSentHeader(): bool
    {
        if (\function_exists('headers_sent')) {
            return \headers_sent();
        }
        return false;
    }

    /**
     * Body flushed or not
     * @return bool
     */
    public function hasObFlushed(): bool
    {
        return \ob_get_level() > 0 && \ob_get_length() > 0;
    }

    /**
     * Add header line
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @param int $status_code
     * @return void
     */
    public function emitHeader(string $name, string $value, bool $replace, int $status_code): void
    {
        \header(
            \sprintf('%s: %s', $name, $value),
            $replace,
            $status_code,
        );
    }

    /**
     * Add status line
     * @param string $protocol_version
     * @param int $status_code
     * @param string $reason_phrase
     * @return void
     */
    public function emitStatusLine(string $protocol_version, int $status_code, string $reason_phrase): void
    {
        \header(
            \sprintf(
                'HTTP/%s %d%s',
                $protocol_version,
                $status_code,
                $reason_phrase ? ' ' . $reason_phrase : '',
            ),
            true,
            $status_code,
        );
    }

    /**
     * Echo body
     * @param string|Stringable $body
     * @return void
     */
    public function emitBody(string|\Stringable $body): void
    {
        print (string) $body;
    }
}
