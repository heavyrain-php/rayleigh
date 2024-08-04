<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\ResponseInterface;

/**
 * PSR-7 Response implementation
 * @package Rayleigh\HttpMessage
 */
class Response extends Message implements ResponseInterface
{
    use HasStatusCode;

    /**
     * Constructor
     * @param int $status_code
     * @param array<string, mixed> $headers
     * @param mixed $body
     * @param string $protocol_version
     * @param null|string $reason_phrase
     */
    public function __construct(
        int $status_code = 200,
        array $headers = [],
        mixed $body = null,
        string $protocol_version = '1.1',
        ?string $reason_phrase = null,
    ) {
        parent::__construct($headers);
        if ($status_code < 100 || $status_code > 599) {
            throw new \InvalidArgumentException('Status code must be an integer between 100 and 599');
        }

        $this->status_code = $status_code;
        $this->protocol_version = $protocol_version;
        if ($body !== null && $body !== '') {
            $this->body = new Stream($body);
        }
        $this->reason_phrase = $reason_phrase ?? $this->getDefaultReasonPhrase($status_code);
    }
}
