<?php

declare(strict_types=1);

/**
 * Class Uri
 * @package Rayleigh\HttpMessage
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Uri::class)]
final class UriTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function getValidUris(): array
    {
        return [
            // from https://datatracker.ietf.org/doc/html/rfc3986#section-1.1.2
            'From RFC3986 Examples 1' => ['ftp://ftp.is.co.za/rfc/rfc1808.txt'],
            'From RFC3986 Examples 2' => ['http://www.ietf.org/rfc/rfc2396.txt'],
            'From RFC3986 Examples 3' => ['ldap://[2001:db8::7]/c=GB?objectClass?one'],
            'From RFC3986 Examples 4' => ['mailto:John.Doe@example.com'],
            'From RFC3986 Examples 5' => ['news:comp.infosystems.www.servers.unix'],
            'From RFC3986 Examples 6' => ['tel:+1-816-555-1212'],
            'From RFC3986 Examples 7' => ['telnet://192.0.2.16:80/'],
            'From RFC3986 Examples 8' => ['urn:oasis:names:specification:docbook:dtd:xml:4.1.2'],

            // from https://github.com/Nyholm/psr7/blob/master/tests/UriTest.php
            'From Nyholm Tests 1' => ['urn:path-rootless'],
            'From Nyholm Tests 2' => ['urn:path:with:colon'],
            'From Nyholm Tests 3' => ['urn:/path-absolute'],
            'From Nyholm Tests 4' => ['urn:/'],
            // only scheme with empty path
            'From Nyholm Tests 5' => ['urn:'],
            // only path
            'From Nyholm Tests 6' => ['/'],
            'From Nyholm Tests 7' => ['relative/'],
            'From Nyholm Tests 8' => ['0'],
            // same document reference
            'From Nyholm Tests 9' => [''],
            // network path without scheme
            'From Nyholm Tests 10' => ['//example.org'],
            'From Nyholm Tests 11' => ['//example.org/'],
            'From Nyholm Tests 12' => ['//example.org?q#h'],
            // only query
            'From Nyholm Tests 13' => ['?q'],
            'From Nyholm Tests 14' => ['?q=abc&foo=bar'],
            // only fragment
            'From Nyholm Tests 15' => ['#fragment'],
            // dot segments are not removed automatically
            'From Nyholm Tests 16' => ['./foo/../bar'],
        ];
    }

    #[Test]
    #[DataProvider('getValidUris')]
    public function testWithPopularSamples(string $validUri): void
    {
        $uri = new Uri($validUri);

        self::assertSame($validUri, (string) $uri);
    }
}
