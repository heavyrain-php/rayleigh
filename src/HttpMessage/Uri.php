<?php

declare(strict_types=1);

/**
 * Class Uri
 * @package Rayleigh\HttpMessage
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Rayleigh\HttpMessage\Internal\UriPartsParser;
use Rayleigh\HttpMessage\Internal\UriScheme;
use JsonSerializable;
use Psr\Http\Message\UriInterface;
use Stringable;

/**
 * PSR-7 URI implementation
 * @package Rayleigh\HttpMessage
 * @see https://datatracker.ietf.org/doc/html/rfc3986#section-3
 *
 * ```
 *          foo://example.com:8042/over/there?name=ferret#nose
 *         \_/   \______________/\_________/ \_________/ \__/
 *          |           |            |            |        |
 *       scheme     authority       path        query   fragment
 *          |   _____________________|__
 *         / \ /                        \
 *         urn:example:animal:ferret:nose
 * ```
 */
class Uri implements UriInterface, Stringable, JsonSerializable
{
    private readonly string $scheme;
    private readonly string $user;
    private readonly string $pass;
    private readonly string $host;
    private readonly ?int $port;
    private readonly string $path;
    private readonly string $query;
    private readonly string $fragment;

    /**
     * Create new Uri instance
     * @param string|Stringable|array<array-key, mixed> $uri
     */
    public function __construct(string|Stringable|array $uri = '')
    {
        $parts = \is_array($uri) ? UriPartsParser::parseFromArray($uri) : UriPartsParser::parseFromString((string) $uri);
        $this->scheme = $parts['scheme'];
        $this->user = $parts['user'];
        $this->pass = $parts['pass'];
        $this->host = $parts['host'];
        $this->port = $parts['port'];
        $this->path = $parts['path'];
        $this->query = $parts['query'];
        $this->fragment = $parts['fragment'];
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        $authority = $this->host;
        $userInfo = $this->getUserInfo();

        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        if ($this->user === '') {
            return '';
        }
        if ($this->pass !== '') {
            // https://datatracker.ietf.org/doc/html/rfc3986#section-3.2.1
            // Use of the format "user:password" in the userinfo field is
            // deprecated.  Applications should not render as clear text any data
            // after the first colon (":") character found within a userinfo
            // subcomponent unless the data after the colon is the empty string
            // (indicating no password). Applications may choose to ignore or
            // reject such data when it is received as part of a reference and
            // should reject the storage of such data in unencrypted form.  The
            // passing of authentication information in clear text has proven to be
            // a security risk in almost every case where it has been used.
            return $this->user . ':' . $this->pass;
        }
        return $this->user;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * Retrieve the port component of the URI
     * If no port is present, but a scheme is present, this method return the standard port for that scheme.
     * @return ?int
     */
    public function getPortOrDefault(): ?int
    {
        if ($this->port !== null) {
            return $this->port;
        }
        return UriScheme::getDefaultPort($this->scheme);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('scheme')));
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $userInfo = $user . ($password !== null ? ':' . $password : '');
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('userInfo')));
    }

    public function withHost(string $host): UriInterface
    {
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('host')));
    }

    public function withPort(?int $port): UriInterface
    {
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('port')));
    }

    public function withPath(string $path): UriInterface
    {
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('path')));
    }

    public function withQuery(string $query): UriInterface
    {
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('query')));
    }

    public function withFragment(string $fragment): UriInterface
    {
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('fragment')));
    }

    public function __toString(): string
    {
        throw new \LogicException('Not implemented');
    }

    /**
     * @return array{scheme: string, user: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public function jsonSerialize(): mixed
    {
        return [
            'scheme' => $this->scheme,
            'user' => $this->user,
            // 'pass' => $this->pass, // Ignore for security risk
            'host' => $this->host,
            'port' => $this->port,
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment,
        ];
    }
}
