<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\HttpMessage\HeaderBag;
use Rayleigh\HttpMessage\Internal\UriPartsParser;
use Rayleigh\HttpMessage\Request;
use Rayleigh\HttpMessage\Stream;
use Rayleigh\HttpMessage\Uri;

/**
 * Class RequestTest
 * @package Rayleigh\HttpMessage\Tests
 */
#[CoversClass(HeaderBag::class)]
#[CoversClass(Request::class)]
#[CoversClass(Stream::class)]
#[CoversClass(UriPartsParser::class)]
#[CoversClass(Uri::class)]
final class RequestTest extends TestCase
{
    #[Test]
    public function testHasMethod(): void
    {
        $request = new Request('gEt', 'http://example.com');

        self::assertSame('gEt', $request->getMethod());

        $new_request = $request->withMethod('POST');

        self::assertNotSame($request, $new_request);
        self::assertSame('POST', $new_request->getMethod());
    }

    #[Test]
    public function testHasUri(): void
    {
        $request = new Request('GET', 'http://example.com');

        self::assertSame('http://example.com', (string) $request->getUri());
        self::assertTrue($request->hasHeader('Host'));
        self::assertSame('example.com', $request->getHeaderLine('Host'));

        $new_request = $request->withUri(new Uri('https://example2.com:8443'), false);

        self::assertNotSame($request, $new_request);
        self::assertTrue($new_request->hasHeader('Host'));
        self::assertSame('example2.com:8443', $new_request->getHeaderLine('Host'));
        self::assertSame('https://example2.com:8443', (string) $new_request->getUri());

        $new_request2 = $new_request->withUri(new Uri('https://example3.com'), true);

        self::assertNotSame($new_request, $new_request2);
        self::assertTrue($new_request2->hasHeader('Host'));
        self::assertSame('example2.com:8443', $new_request2->getHeaderLine('Host'), 'Host header should be preserved');

        $new_request3 = new Request('GET', 'https://example3.com', ['Host' => 'example4.com']);

        self::assertSame('example4.com', $new_request3->getHeaderLine('Host'));

        $new_request4 = new Request('GET', 'https://example5.com', ['Accept' => '*']);

        $headers = $new_request4->getHeaders();
        self::assertSame('host', \key($headers), 'Host must be first');

        $new_request5 = new Request('GET', '');

        self::assertSame('', (string) $new_request5->getUri());
    }

    #[Test]
    public function testHasBody(): void
    {
        $request = new Request('GET', 'https://example.com');

        self::assertSame('', (string) $request->getBody());

        $request2 = $request->withBody(new Stream('test'));

        self::assertNotSame($request, $request2);
        self::assertSame('test', (string) $request2->getBody());

        $request3 = new Request('POST', 'https://example2.com', [], 'test2');

        self::assertSame('test2', (string) $request3->getBody());
    }

    #[Test]
    public function testInvalidRequestTarget(): void
    {
        $this->expectExceptionMessage('Invalid request target provided; cannot contain whitespace');

        $request = new Request('GET', 'https://example.com');

        $request->withRequestTarget("/path with whitespace!\r\n");
    }

    #[Test]
    public function testHasRequestTarget(): void
    {
        $request = new Request('GET', 'https://example.com');

        self::assertSame('/', $request->getRequestTarget());

        $request2 = $request->withRequestTarget('/path');

        self::assertSame('/path', $request2->getRequestTarget());

        $request3 = new Request('GET', 'https://example.com/path?query');

        self::assertSame('/path?query', $request3->getRequestTarget());
    }
}
