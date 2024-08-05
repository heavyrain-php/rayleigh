<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-7 ServerRequest implementation
 * @package Rayleigh\HttpMessage
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    use HasAttributes;
    use HasParams;
    use HasParsedBody;
    use HasUploadedFiles;

    /**
     * ServerRequest constructor
     * @param string $method
     * @param string|UriInterface $uri
     * @param array<string, mixed> $headers
     * @param StreamInterface|resource|string|null $body
     * @param string $protocol_version
     * @param array<string, mixed> $server_params
     * @param array<string, string> $cookie_params
     * @param array<array-key, mixed> $query_params
     * @param array<string, UploadedFileInterface> $uploaded_files
     * @param array<array-key, mixed>|object|null $parsed_body
     * @param array<array-key, mixed> $attributes
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $method,
        string|UriInterface $uri = '',
        array $headers = [],
        mixed $body = '',
        string $protocol_version = '1.1',
        array $server_params = [],
        array $cookie_params = [],
        array $query_params = [],
        array $uploaded_files = [],
        array|object|null $parsed_body = null,
        array $attributes = [],
    ) {
        parent::__construct($method, $uri, $headers, $body, $protocol_version);
        $this->server_params = $server_params;
        $this->cookie_params = $cookie_params;
        /** @var array<array-key, mixed> $query_from_uri */
        $query_from_uri = [];
        \parse_str($this->uri->getQuery(), $query_from_uri);
        foreach ($query_from_uri as $name => $value) {
            if (\is_int($name)) {
                $this->query_params[] = $value; // @codeCoverageIgnore
            } else {
                $this->query_params[$name] = $value;
            }
        }
        foreach ($query_params as $name => $value) {
            if (\is_int($name)) {
                $this->query_params[] = $value;
            } else {
                $this->query_params[$name] = $value;
            }
        }
        foreach ($uploaded_files as $name => $uploaded_file) {
            if (!$uploaded_file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid uploaded file');
            }
            $this->uploaded_files[$name] = $uploaded_file;
        }
        $this->parsed_body = $parsed_body;
        $this->attributes = $attributes;
    }
}
