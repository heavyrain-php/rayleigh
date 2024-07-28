<?php

declare(strict_types=1);

/**
 * Class Uri
 * @package Rayleigh\HttpMessage
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Internal;

/**
 * URI Scheme
 * @package Rayleigh\HttpMessage\Internal
 * @internal
 */
final class UriScheme
{
    private const SCHEME_PORT_MAP = [
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

    /**
     * Get default major port number by scheme
     * @param string $scheme
     * @return null|int
     */
    public static function getDefaultPort(string $scheme): ?int
    {
        return \array_key_exists($scheme, self::SCHEME_PORT_MAP) ? self::SCHEME_PORT_MAP[$scheme] : null;
    }
}
