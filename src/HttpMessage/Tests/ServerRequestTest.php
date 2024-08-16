<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Rayleigh\HttpMessage\HasAttributes;
use Rayleigh\HttpMessage\HeaderBag;
use Rayleigh\HttpMessage\HasParams;
use Rayleigh\HttpMessage\HasParsedBody;
use Rayleigh\HttpMessage\HasUploadedFiles;
use Rayleigh\HttpMessage\Internal\UriPartsParser;
use Rayleigh\HttpMessage\ServerRequest;
use Rayleigh\HttpMessage\Stream;
use Rayleigh\HttpMessage\Uri;

/**
 * Class RequestTest
 * @package Rayleigh\HttpMessage\Tests
 */
#[CoversClass(HeaderBag::class)]
#[CoversClass(ServerRequest::class)]
#[CoversClass(Stream::class)]
#[CoversClass(Uri::class)]
#[CoversClass(UriPartsParser::class)]
#[CoversTrait(HasAttributes::class)]
#[CoversTrait(HasParams::class)]
#[CoversTrait(HasParsedBody::class)]
#[CoversTrait(HasUploadedFiles::class)]
final class ServerRequestTest extends TestCase
{
    #[Test]
    public function testHasAttributes(): void
    {
        $server_request = new ServerRequest('GET');

        self::assertSame([], $server_request->getAttributes());
        self::assertNull($server_request->getAttribute('test'));

        $server_request2 = $server_request->withAttribute('test', 'value');

        self::assertNotSame($server_request, $server_request2);
        self::assertSame(['test' => 'value'], $server_request2->getAttributes());
        self::assertSame('value', $server_request2->getAttribute('test'));

        $server_request3 = $server_request2->withoutAttribute('test');

        self::assertNotSame($server_request2, $server_request3);
        self::assertSame([], $server_request3->getAttributes());
        self::assertNull($server_request3->getAttribute('test'));

        $server_request4 = $server_request3->withoutAttribute('unknown');

        self::assertNotSame($server_request3, $server_request4);
    }

    #[Test]
    public function testHasParams(): void
    {
        $server_request = new ServerRequest('GET');

        self::assertSame([], $server_request->getServerParams());
        self::assertSame([], $server_request->getCookieParams());
        self::assertSame([], $server_request->getQueryParams());

        $server_request2 = $server_request->withCookieParams(['test' => 'value']);

        self::assertNotSame($server_request, $server_request2);
        self::assertSame(['test' => 'value'], $server_request2->getCookieParams());

        $server_request3 = $server_request2->withQueryParams(['test' => 'value']);

        self::assertNotSame($server_request2, $server_request3);
        self::assertSame(['test' => 'value'], $server_request3->getQueryParams());

        $server_request4 = new ServerRequest(
            method: 'GET',
            uri: '',
            headers: [],
            body: '',
            protocol_version: '1.1',
            server_params: ['server' => 'test'],
            cookie_params: ['cookie' => 'test'],
            query_params: ['query' => 'test'],
        );

        self::assertSame(['server' => 'test'], $server_request4->getServerParams());
        self::assertSame(['cookie' => 'test'], $server_request4->getCookieParams());
        self::assertSame(['query' => 'test'], $server_request4->getQueryParams());
    }

    #[Test]
    public function testHasParsedBody(): void
    {
        $server_request = new ServerRequest('GET');

        self::assertNull($server_request->getParsedBody());

        $server_request2 = $server_request->withParsedBody(['test' => 'value']);

        self::assertNotSame($server_request, $server_request2);
        self::assertSame(['test' => 'value'], $server_request2->getParsedBody());
    }

    #[Test]
    public function testParsedBodyFailed(): void
    {
        $this->expectExceptionMessage('Invalid parsed body');

        $body = 1;
        (new ServerRequest('GET'))->withParsedBody($body);
    }

    #[Test]
    public function testHasUploadedFiles(): void
    {
        $file = self::createStub(UploadedFileInterface::class);
        $file2 = self::createStub(UploadedFileInterface::class);

        $server_request = new ServerRequest(method: 'GET', uploaded_files: ['a.txt' => $file, 'b.exe' => $file2]);

        self::assertSame(['a.txt' => $file, 'b.exe' => $file2], $server_request->getUploadedFiles());

        $file3 = self::createStub(UploadedFileInterface::class);

        $server_request2 = $server_request->withUploadedFiles(['c.jpg' => $file3]);

        self::assertNotSame($server_request, $server_request2);
        self::assertSame(['c.jpg' => $file3], $server_request2->getUploadedFiles());
    }

    #[Test]
    public function testInvalidUploadedFile(): void
    {
        $file = 'invalid';

        $this->expectExceptionMessage('Invalid uploaded file');

        // @phpstan-ignore argument.type
        new ServerRequest(method: 'GET', uploaded_files: ['a.txt' => $file]);
    }

    #[Test]
    public function testInvalidUploadedFile2(): void
    {
        $file = 'invalid';

        $this->expectExceptionMessage('Invalid uploaded file');

        /** phpstan-ignore-next-line */
        (new ServerRequest('GET'))->withUploadedFiles(['a.txt' => $file]);
    }

    #[Test]
    public function testConstructor(): void
    {
        $server_request = new ServerRequest(
            method: 'POST',
            uri: '?fromuri=1&=test',
            headers: [],
            body: '',
            protocol_version: '1.1',
            query_params: ['test' => 'value', 'value2'],
        );

        self::assertSame(['fromuri' => '1', 'test' => 'value', 0 => 'value2'], $server_request->getQueryParams());
    }
}
