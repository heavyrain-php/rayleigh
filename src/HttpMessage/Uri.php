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
/* readonly */ class Uri implements UriInterface, Stringable, JsonSerializable
{
    /**
     * Well-known scheme port map
     */
    protected const WELL_KNOWN_SCHEME_PORT_MAP = [
        'ftp' => 20,
        'ssh' => 22,
        'telnet' => 23,
        'smtp' => 25,
        'dns' => 53,
        'http' => 80,
        'pop' => 110,
        'nntp' => 119,
        'imap' => 143,
        'ldap' => 389,
        'https' => 443,
    ];

    protected readonly string $scheme;
    protected readonly string $user;
    protected readonly string $pass;
    protected readonly string $host;
    protected readonly ?int $port;
    protected readonly string $path;
    protected readonly string $query;
    protected readonly string $fragment;

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

        // validate state
        if ($this->getAuthority() === '') {
            if (\str_starts_with($this->path, '//')) {
                throw new MalformedUriException('Invalid URI: Authority is required when path starts with "//"');
            }
            if ($this->scheme === '' && \strpos(\explode('/', $this->path, 2)[0], ':') !== false) {
                throw new MalformedUriException('Invalid URI: Scheme is required when path contains ":"');
            }
        }
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
        if (\array_key_exists($this->scheme, self::WELL_KNOWN_SCHEME_PORT_MAP)) {
            return self::WELL_KNOWN_SCHEME_PORT_MAP[$this->scheme];
        }
        return null;
    }

    /**
     * Check if the URI uses the default port for the scheme
     * @return bool
     */
    public function isDefaultPort(): bool
    {
        $hasDefaultPort = $this->hasDefaultPort();

        if ($hasDefaultPort) {
            $defaultPort = self::WELL_KNOWN_SCHEME_PORT_MAP[$this->scheme];
            return $this->port === null || $this->port === $defaultPort;
        }
        return false;
    }

    public function hasDefaultPort(): bool
    {
        return \array_key_exists($this->scheme, self::WELL_KNOWN_SCHEME_PORT_MAP);
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
        $pass = $password === null ? '' : $password;
        // @phpstan-ignore-next-line
        return new self(UriPartsParser::parseWithNewParts($this, compact('user', 'pass')));
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
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '' || $this->scheme === 'file') {
            $uri .= '//' . $authority;
        }

        $path = $this->path;
        if ($authority !== '' && $path !== '' && $path[0] !== '/') {
            $path = '/' . $path;
        }

        $uri .= $path;

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function jsonSerialize(): mixed
    {
        return $this->__toString();
    }
}
