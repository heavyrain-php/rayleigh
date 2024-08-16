<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Rayleigh\HttpMessage\MalformedUriException;
use Rayleigh\HttpMessage\ServerRequest;
use Rayleigh\HttpMessage\UploadedFile;
use Rayleigh\HttpMessage\Uri;
use RuntimeException;

/**
 * PSR-7 ServerRequest builds from global variables
 * @package Rayleigh\HttpServer
 * @link https://github.com/laminas/laminas-diactoros/blob/3.4.x/src/ServerRequestFactory.php
 * @example
 * ```php
 * $middlewares = [snip...];
 * $server_request = TraditionalServerRequestBuilder::build();
 * $response = (new ServerRequestRunner($middlewares))->handle($server_request);
 * (new ResponseEmitter(new Emitter()))->emit($response);
 * ```
 */
final /* readonly */ class TraditionalServerRequestBuilder
{
    /**
     * This class cannot be instantiated
     */
    private function __construct() {}

    /**
     * Build ServerRequest from global variables
     * @param array<non-empty-string, scalar> $server
     * @param array<non-empty-string, scalar> $cookie
     * @param array<non-empty-string, array{name: ?string, type: ?string, size: ?int, tmp_name: ?string, error: ?int, full_path: ?string}> $files
     * @param array<non-empty-string, scalar> $get
     * @param array<non-empty-string, scalar> $post
     * @param array<non-empty-string, scalar> $request
     * @return ServerRequestInterface
     * @throws MalformedUriException
     */
    public static function build(
        array $server = $_SERVER,
        array $cookie = $_COOKIE,
        array $files = $_FILES,
        array $get = $_GET,
        array $post = $_POST,
        array $request = $_REQUEST,
    ): ServerRequestInterface {
        $method = self::generateMethod($server, $get, $post);
        switch (\strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (\array_key_exists('CONTENT_TYPE', $server) === false) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded'; // Override default content type
                }
                break;
            case 'PATCH':
                break; // Do nothing
            default:
                $request = []; // clear request
                break;
        }

        return new ServerRequest(
            method: $method,
            uri: self::generateUri($server),
            headers: self::generateHeaders($server),
            body: self::generateBody($method),
            protocol_version: self::generateProtocolVersion($server),
            server_params: $server,
            cookie_params: self::generateCookieParams($cookie),
            query_params: self::generateQueryParams($method, $get, $post, $request),
            uploaded_files: self::generateUploadedFiles($files),
            parsed_body: null, // default null
            attributes: [], // default empty
        );
    }

    /**
     * Generate method from $_SERVER['REQUEST_METHOD'], preserve case
     * @param array<string, scalar> $server
     * @param array<string, scalar> $get
     * @param array<string, scalar> $post
     * @return string
     * @throws RuntimeException
     */
    private static function generateMethod(array $server, array $get, array $post): string
    {
        if ($method = self::findStringFromArray($server, 'HTTP_X_HTTP_METHOD_OVERRIDE')) {
            return $method;
        }
        if ($method = self::findStringFromArray($post, '_method')) {
            return $method;
        }
        if ($method = self::findStringFromArray($get, '_method')) {
            return $method;
        }
        if ($method = self::findStringFromArray($server, 'REQUEST_METHOD')) {
            return $method;
        }
        throw new RuntimeException('REQUEST_METHOD is invalid');
    }

    /**
     * find and satisfies the value is string
     * @param array<string, scalar> $str
     * @param string $key
     * @return null|string
     */
    private static function findStringFromArray(array $str, string $key): ?string
    {
        if (\array_key_exists($key, $str)) {
            $value = $str[$key];
            if (\is_string($value)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param array<string, scalar> $server
     * @return UriInterface
     * @throws MalformedUriException
     * @link http://www.faqs.org/rfcs/rfc3875.html
     */
    private static function generateUri(array $server): UriInterface
    {
        $uri = new Uri();
        $server_protocol = $server['SERVER_PROTOCOL'] ?? '';
        $https = $server['HTTPS'] ?? '';
        if (\is_string($https) && $https !== '') {
            $uri = $uri->withScheme('https');
        } elseif (\is_string($server_protocol)) {
            if (\str_starts_with($server_protocol, 'HTTP/')) {
                $uri = $uri->withScheme('http');
            }
        }
        $user = $server['PHP_AUTH_USER'] ?? null;
        $pass = $server['PHP_AUTH_PASS'] ?? $server['PHP_AUTH_PW'] ?? null;
        if (\is_string($user) && (\is_string($pass) || $pass === null)) {
            $uri = $uri->withUserInfo($user, $pass);
        }
        $uri = $uri->withHost($_SERVER['SERVER_NAME'] ?? 'localhost');
        $uri = $uri->withPort($_SERVER['SERVER_PORT'] ?? ($uri->getScheme() === 'https' ? 443 : 80));
        $uri = $uri->withPath($_SERVER['SCRIPT_NAME'] ?? '/');
        $uri = $uri->withQuery($_SERVER['QUERY_STRING'] ?? '');
        return $uri;
    }

    /**
     * Generate headers from $_SERVER['HTTP_*']
     * @param array<string, scalar> $server
     * @return array<string, scalar[]>
     */
    private static function generateHeaders(array $server): array
    {
        $headers = [];

        if ($content_type = self::findStringFromArray($server, 'CONTENT_TYPE')) {
            $headers['Content-Type'] = [$content_type];
        }
        if ($content_length = self::findStringFromArray($server, 'CONTENT_LENGTH')) {
            $headers['Content-Length'] = [$content_length];
        }

        foreach ($server as $key => $value) {
            if (\str_starts_with($key, 'HTTP_')) {
                $header_name = \ucwords(\strtolower(\str_replace('_', '-', \substr($key, 5))));
                if (\array_key_exists($header_name, $headers) === false) {
                    $headers[$header_name] = [];
                }
                $headers[$header_name][] = $value;
            }
        }

        return $headers;
    }

    /**
     * @param string $method
     * @return resource|null
     */
    private static function generateBody(string $method): mixed
    {
        $body = match (\strtoupper($method)) {
            'POST', 'PUT', 'DELETE', 'PATCH' => \fopen('php://input', 'r'),
            default => null,
        };
        \assert($body === null || \is_resource($body)); // for phpstan
        return $body;
    }

    /**
     * @param array<string, scalar> $server
     * @return string
     */
    private static function generateProtocolVersion(array $server): string
    {
        $server_protocol = self::findStringFromArray($server, 'SERVER_PROTOCOL');
        if ($server_protocol !== null && \str_starts_with($server_protocol, 'HTTP/')) {
            return \substr($server_protocol, 5);
        }
        throw new RuntimeException(\sprintf('Unknown server protocol: %s', $server_protocol));
    }

    /**
     * @param array<string, scalar> $cookie
     * @return array<string, string>
     */
    private static function generateCookieParams(array $cookie): array
    {
        $cookies = [];
        foreach ($cookie as $name => $value) {
            if (\is_string($value)) {
                $cookies[$name] = $value;
            }
        }

        return $cookies;
    }

    /**
     * @param string $method
     * @param array<string, scalar> $get
     * @param array<string, scalar> $post
     * @param array<string, scalar> $request
     * @return array<string, scalar>
     */
    private static function generateQueryParams(string $method, array $get, array $post, array $request): array
    {
        return match (\strtoupper($method)) {
            'GET' => $get,
            'POST', 'PUT', 'DELETE', 'PATCH' => $post,
            default => [],
        };
    }

    /**
     * Generate UploadedFiles from $_FILES
     * @param array<string, array{name: ?string, type: ?string, size: ?int, tmp_name: ?string, error: ?int, full_path: ?string}> $files
     * @return array<string, UploadedFileInterface>
     */
    private static function generateUploadedFiles(array $files): array
    {
        $uploaded_files = [];
        foreach ($files as $name => $file) {
            $uploaded_files[$name] = new UploadedFile(
                $file['tmp_name'] ?? throw new RuntimeException('$_FILES tmp_name is required'),
                $file['size'],
                $file['error'] ?? throw new RuntimeException('$_FILES error is required'),
                $file['name'],
                $file['type'],
            );
        }

        return $uploaded_files;
    }
}
