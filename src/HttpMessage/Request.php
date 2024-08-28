<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Request implementation
 * @package Rayleigh\HttpMessage
 */
class Request extends Message implements RequestInterface
{
    use HasMethod;
    use HasRequestTarget;
    use HasUri;

    /**
     * Constructor
     * @param string $method
     * @param string|UriInterface $uri
     * @param array<string, mixed> $headers
     * @param string|resource|StreamInterface|null $body
     * @param string $protocol_version
     * @return void
     */
    public function __construct(
        string $method,
        string|UriInterface $uri,
        array $headers = [],
        mixed $body = null,
        string $protocol_version = '1.1',
    ) {
        parent::__construct($headers);

        // case-sensitive
        $this->method = $method;
        $this->uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        $this->validateProtocolVersion($protocol_version);
        $this->protocol_version = $protocol_version;
        if ($body !== null && $body !== '') {
            $this->body = new Stream($body);
        }

        $this->header_bag->updateHostHeaderFromUri($this->uri);
    }
}
