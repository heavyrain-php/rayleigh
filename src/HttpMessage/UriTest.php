<?php

declare(strict_types=1);

/**
 * Class UriTest
 * @package Rayleigh\HttpMessage
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\HttpMessage\Internal\UriPartsParser;

#[CoversClass(Uri::class)]
#[CoversClass(UriPartsParser::class)]
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

            // Plus alpha
            'Plus alpha 1' => ['https://日本語.com'],
            'Plus alpha 3' => ['file:///tmp/filename.txt'],
            'Plus alpha 4' => ['http://[2a00:f48:1008::212:183:10]:56?foo=bar'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function getInvalidUris(): array
    {
        return [
            'Valid in other libraries' => ['0://0:0@0/0?0#0'], // It is invalid scheme
            'Only http Scheme' => ['http://'],
            'Host with colon' => ['urn://host:with:colon/'],
            'UTF-8 Scheme' => ['日本語://example.com'],
            'No authority but multiple slashes' => ['file:////test'],
        ];
    }

    #[Test]
    #[DataProvider('getValidUris')]
    public function testWithValidUris(string $validUri): void
    {
        $uri = new Uri($validUri);

        self::assertSame($validUri, (string) $uri);
    }

    #[Test]
    #[DataProvider('getInvalidUris')]
    public function testWithInvalidUris(string $invalidUri): void
    {
        $this->expectException(MalformedUriException::class);

        new Uri($invalidUri);
    }

    #[Test]
    public function testWithUpperCasedUri(): void
    {
        $uri = 'HtTPs://ExAmPlE.cOm:8443/PaTh?QuErY=VaLuE#FrAgMeNt';
        $expected = 'https://example.com:8443/PaTh?QuErY=VaLuE#FrAgMeNt';
        $actual = (string) new Uri($uri);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function testGetDefaultPort(): void
    {
        $uri = 'https://example.com';
        $expected = 443;
        $instance = new Uri($uri);
        $actual = $instance->getPortOrDefault();

        self::assertSame($expected, $actual);
        self::assertTrue($instance->isDefaultPort());
    }

    #[Test]
    public function testGetDefaultPortSpecified(): void
    {
        $uri = 'unknown-uri://example.com:1234';
        $expected = 1234;
        $instance = new Uri($uri);
        $actual = $instance->getPortOrDefault();

        self::assertSame($expected, $actual);
        self::assertFalse($instance->isDefaultPort());
    }

    #[Test]
    public function testGetDefaultPortUnknown(): void
    {
        $uri = 'unknown-uri://example.com';
        $expected = null;
        $instance = new Uri($uri);
        $actual = $instance->getPortOrDefault();

        self::assertSame($expected, $actual);
        self::assertFalse($instance->isDefaultPort());
    }

    /**
     * @return array<string, array{input: string, path: string, query: string, fragment: string, output: string}>
     */
    public static function getEncodingProperlyUris(): array
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';

        return [
            // from https://github.com/guzzle/psr7/blob/124aab5a1fa6adefb77a4ea51ada3804d49c278d/tests/UriTest.php#L534
            'Percent encode spaces' => [
                'input' => '/pa th?q=va lue#frag ment',
                'path' => '/pa%20th',
                'query' => 'q=va%20lue',
                'fragment' => 'frag%20ment',
                'output' => '/pa%20th?q=va%20lue#frag%20ment',
            ],
            'Percent encode multibyte' => [
                'input' => '/€?€#€',
                'path' => '/%E2%82%AC',
                'query' => '%E2%82%AC',
                'fragment' => '%E2%82%AC',
                'output' => '/%E2%82%AC?%E2%82%AC#%E2%82%AC',
            ],
            'Don\'t encode something that\'s already encoded' => [
                'input' => '/pa%20th?q=va%20lue#frag%20ment',
                'path' => '/pa%20th',
                'query' => 'q=va%20lue',
                'fragment' => 'frag%20ment',
                'output' => '/pa%20th?q=va%20lue#frag%20ment',
            ],
            'Percent encode invalid percent encodings' => [
                'input' => '/pa%2-th?q=va%2-lue#frag%2-ment',
                'path' => '/pa%252-th',
                'query' => 'q=va%252-lue',
                'fragment' => 'frag%252-ment',
                'output' => '/pa%252-th?q=va%252-lue#frag%252-ment',
            ],
            'Don\'t encode path segments' => [
                'input' => '/pa/th//two?q=va/lue#frag/ment',
                'path' => '/pa/th//two',
                'query' => 'q=va/lue',
                'fragment' => 'frag/ment',
                'output' => '/pa/th//two?q=va/lue#frag/ment',
            ],
            'Don\'t encode unreserved chars or sub-delimiters' => [
                'input' => "/$unreserved?$unreserved#$unreserved",
                'path' => "/$unreserved",
                'query' => $unreserved,
                'fragment' => $unreserved,
                'output' => "/$unreserved?$unreserved#$unreserved",
            ],
            'Encoded unreserved chars are not decoded' => [
                'input' => '/p%61th?q=v%61lue#fr%61gment',
                'path' => '/p%61th',
                'query' => 'q=v%61lue',
                'fragment' => 'fr%61gment',
                'output' => '/p%61th?q=v%61lue#fr%61gment',
            ],
        ];
    }

    #[Test]
    #[DataProvider('getEncodingProperlyUris')]
    public function testEncodingProperlyUris(string $input, string $path, string $query, string $fragment, string $output): void
    {
        $uri = new Uri($input);
        self::assertSame($path, $uri->getPath());
        self::assertSame($query, $uri->getQuery());
        self::assertSame($fragment, $uri->getFragment());
        self::assertSame($output, (string) $uri);
    }

    #[Test]
    public function testJsonSerialize(): void
    {
        $uri = new Uri('https://example.com');
        $expected = '{"uri":"https:\/\/example.com"}';
        $actual = \json_encode(\compact('uri'));

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function testWithMethods(): void
    {
        $uri = new Uri();

        self::assertNotsame($uri, $uri->withScheme('https'));
        self::assertNotSame($uri, $uri->withUserInfo('user', 'password'));
        self::assertNotSame($uri, $uri->withHost('example.com'));
        self::assertNotSame($uri, $uri->withPort(8443));
        self::assertNotSame($uri, $uri->withPath('/path'));
        self::assertNotSame($uri, $uri->withQuery('query'));
        self::assertNotSame($uri, $uri->withFragment('fragment'));
    }

    #[Test]
    public function testGetProperties(): void
    {
        $uri = new Uri();

        self::assertSame('', $uri->getScheme());
        self::assertSame('', $uri->getUserInfo());
        self::assertSame('', $uri->getHost());
        self::assertNull($uri->getPort());
        self::assertSame('', $uri->getAuthority());
        self::assertSame('', $uri->getPath());
        self::assertSame('', $uri->getQuery());
        self::assertSame('', $uri->getFragment());
    }

    #[Test]
    public function testGetUserInfo(): void
    {
        $uri = new Uri('https://example.com');
        self::assertSame('', $uri->getUserInfo());

        $uri = new Uri('https://anonymous@example.com');
        self::assertSame('anonymous', $uri->getUserInfo());

        $uri = new Uri('https://anonymous:password@example.com');
        self::assertSame('anonymous:password', $uri->getUserInfo());
    }

    #[Test]
    public function testToStringFilteredPath(): void
    {
        $expected = 'https://example.com/:../';

        $uri = (new Uri())
            ->withScheme('https')
            ->withPath(':../')
            ->withHost('example.com');

        self::assertSame($expected, $uri->__toString());
    }
}
