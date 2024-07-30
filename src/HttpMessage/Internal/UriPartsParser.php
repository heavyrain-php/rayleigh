<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Internal;

use Rayleigh\HttpMessage\MalformedUriException;
use Rayleigh\HttpMessage\Uri;

/**
 * Parse URI to parts
 * @package Rayleigh\HttpMessage\Internal
 * @internal
 */
final class UriPartsParser
{
    /**
     * Sub deliminations characters
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.3
     */
    private const SUB_DELIMS_CHARACTERS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters
     * @link https://datatracker.ietf.org/doc/html/rfc3986#section-2.3
     */
    private const UNRESERVED_CHARACTERS = 'a-zA-Z0-9\-\._~';

    /**
     * Parse any array to URI parts
     * @param array<array-key, mixed> $parts
     * @return array{scheme: string, user: string, pass: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public static function parseFromArray(array $parts): array
    {
        return [
            'scheme' => \array_key_exists('scheme', $parts) ? self::filterScheme($parts['scheme']) : '',
            'user' => \array_key_exists('user', $parts) ? self::filterUser($parts['user']) : '',
            'pass' => \array_key_exists('pass', $parts) ? self::filterPass($parts['pass']) : '',
            'host' => \array_key_exists('host', $parts) ? self::filterHost($parts['host']) : '',
            'port' => \array_key_exists('port', $parts) ? self::filterPort($parts['port']) : null,
            'path' => \array_key_exists('path', $parts) ? self::filterPath($parts['path']) : '',
            'query' => \array_key_exists('query', $parts) ? self::filterQuery($parts['query']) : '',
            'fragment' => \array_key_exists('fragment', $parts) ? self::filterFragment($parts['fragment']) : '',
        ];
    }

    /**
     * Parse URI string to parts
     * @param string $uri
     * @return array{scheme: string, user: string, pass: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public static function parseFromString(string $uri): array
    {
        return self::parseFromArray(self::parseUrl($uri));
    }

    /**
     * Parse new URI parts with Uri instance
     * @param Uri $uri Current Uri
     * @param array<array-key, mixed> $newParts
     * @return array{scheme: string, user: string, pass: string, host: string, port: ?int, path: string, query: string, fragment: string}
     */
    public static function parseWithNewParts(Uri $uri, array $newParts): array
    {
        return self::parseFromArray([...self::parseFromString(((string) $uri)), ...$newParts]);
    }

    /**
     * It respects GuzzleHTTP's parser for multibyte domain names.
     * @see https://www.php.net/manual/en/function.parse-url.php#114817
     * @see https://github.com/guzzle/psr7/blob/38ef514a6c21335f29d9be64b097d2582ecbf8e4/src/Uri.php#L106
     * @param string $url
     * @return array<string, string>
     * @throws MalformedUriException
     */
    private static function parseUrl(string $url): array
    {
        // @see https://github.com/guzzle/psr7/pull/403
        /** @var string[] $matches */
        $matches = [];
        $prefix = null;
        $urlMayExceptPrefix = $url;
        $foundIpv6 = \preg_match(
            '%^(.*://\[[0-9:a-f]+\])(.*?)$%', // Whether host is IPv6
            $url,
            $matches,
        );
        if ($foundIpv6) {
            // The host is IPv6
            \assert(\count($matches) === 3);
            $prefix = $matches[1];
            $urlMayExceptPrefix = $matches[2];
        }

        $encodedUrl = \preg_replace_callback(
            '%[^:/@?&=#]+%usD', // multibyte characters
            static fn (array $matches): string => \urlencode($matches[0]),
            $urlMayExceptPrefix,
        );

        $parseResult = \parse_url($prefix . $encodedUrl);

        if ($parseResult === false) {
            throw new MalformedUriException('Seriously malformed URI has provided: ' . $url);
        }

        return \array_map(
            static fn (string|int $value): string => \urldecode((string)$value),
            $parseResult,
        );
    }

    /**
     * Filter scheme part
     * It does not reject unknown scheme. It only trims and lower case.
     * @param mixed $scheme
     * @return string
     */
    private static function filterScheme(mixed $scheme): string
    {
        if (\is_string($scheme)) {
            if ($scheme === '') {
                return '';
            }
            $scheme = \strtolower(\rtrim($scheme, ':/'));
            // @link https://datatracker.ietf.org/doc/html/rfc3986#section-3.1
            // scheme = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
            if (1 !== \preg_match('/[a-z][^0-9a-z\+\-\.]*/', $scheme)) {
                // Detect invalid characters
                throw new MalformedUriException('Invalid scheme provided: ' . $scheme);
            }
            return $scheme;
        }
        return '';
    }

    private static function filterUser(mixed $user): string
    {
        if (\is_string($user)) {
            return self::urlSafeString($user);
        }
        return '';
    }

    private static function filterPass(mixed $pass): string
    {
        if (\is_string($pass)) {
            return self::urlSafeString($pass);
        }
        return '';
    }

    private static function filterHost(mixed $host): string
    {
        if (\is_string($host)) {
            return \strtolower($host);
        }
        return '';
    }

    private static function filterPort(mixed $port): ?int
    {
        if ($port === null) {
            return null;
        }
        // @phpstan-ignore-next-line
        $port = \intval($port);
        if ($port < 0 || $port > 65535) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid port: %d. Must be between 0 and 65535', $port),
            );
        }
        return $port;
    }

    private static function filterPath(mixed $path): string
    {
        if (\is_string($path)) {
            return self::urlSafeString($path);
        }
        return '';
    }

    private static function filterQuery(mixed $query): string
    {
        if (\is_string($query)) {
            return self::urlSafeString($query);
        }
        return '';
    }

    private static function filterFragment(mixed $fragment): string
    {
        if (\is_string($fragment)) {
            return self::urlSafeString($fragment);
        }
        return '';
    }

    private static function urlSafeString(string $str): string
    {
        $result = \preg_replace_callback(
            // safe characters or percent-encoded characters
            '/(?:[^' . self::UNRESERVED_CHARACTERS . self::SUB_DELIMS_CHARACTERS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            static fn (array $matches): string => \rawurlencode($matches[0]),
            $str,
        );

        if ($result === null) {
            throw new \RuntimeException('Failed to encode URL string: ' . $str); // @codeCoverageIgnore
        }

        return $result;
    }
}
