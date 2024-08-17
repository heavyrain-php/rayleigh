<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpFactory\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use PHPUnit\Framework\TestCase;
use Rayleigh\HttpFactory\RequestFactory;
use Rayleigh\HttpFactory\ResponseFactory;
use Rayleigh\HttpFactory\ServerRequestFactory;
use Rayleigh\HttpFactory\StreamFactory;
use Rayleigh\HttpFactory\UploadedFileFactory;
use Rayleigh\HttpFactory\UriFactory;
use Rayleigh\HttpMessage\HasMethod;
use Rayleigh\HttpMessage\HasStatusCode;
use Rayleigh\HttpMessage\HasUri;
use Rayleigh\HttpMessage\HeaderBag;
use Rayleigh\HttpMessage\Internal\UriPartsParser;
use Rayleigh\HttpMessage\Message;
use Rayleigh\HttpMessage\Response;
use Rayleigh\HttpMessage\ServerRequest;
use Rayleigh\HttpMessage\Stream;
use Rayleigh\HttpMessage\UploadedFile;
use Rayleigh\HttpMessage\Uri;

/**
 * Class FactoryTest
 * @package Rayleigh\HttpFactory\Tests
 */
#[CoversClass(RequestFactory::class)]
#[CoversClass(ResponseFactory::class)]
#[CoversClass(ServerRequestFactory::class)]
#[CoversClass(StreamFactory::class)]
#[CoversClass(UploadedFileFactory::class)]
#[CoversClass(UriFactory::class)]
#[UsesClass(HeaderBag::class)]
#[UsesClass(Message::class)]
#[UsesClass(Response::class)]
#[UsesClass(ServerRequest::class)]
#[UsesClass(Stream::class)]
#[UsesClass(UploadedFile::class)]
#[UsesClass(Uri::class)]
#[UsesClass(UriPartsParser::class)]
#[UsesTrait(HasMethod::class)]
#[UsesTrait(HasStatusCode::class)]
#[UsesTrait(HasUri::class)]
final class FactoryTest extends TestCase
{
    #[Test]
    public function testRequestFactory(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'https://example.com');

        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com', (string) $request->getUri());
    }

    #[Test]
    public function testResponseFactory(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse(200);

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function testServerRequestFactory(): void
    {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', 'https://example.com');

        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com', (string) $request->getUri());
    }

    #[Test]
    public function testStreamFactory(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('Hello, World!');

        self::assertSame('Hello, World!', (string) $stream);
    }

    #[Test]
    public function testStreamFactoryFromFile(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile(__FILE__);

        self::assertSame(\file_get_contents(__FILE__), (string) $stream);
    }

    #[Test]
    public function testStreamFactoryFromResource(): void
    {
        $factory = new StreamFactory();
        $resource = \fopen(__FILE__, 'r');
        $stream = $factory->createStreamFromResource($resource);

        self::assertSame(\file_get_contents(__FILE__), (string) $stream);
    }

    #[Test]
    public function testUploadedFileFactory(): void
    {
        $factory = new UploadedFileFactory();
        $uploadedFile = $factory->createUploadedFile(new Stream('Hello, World!'), 12, UPLOAD_ERR_OK, 'hello.txt', 'text/plain');

        self::assertSame('Hello, World!', (string) $uploadedFile->getStream());
        self::assertSame(12, $uploadedFile->getSize());
        self::assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        self::assertSame('hello.txt', $uploadedFile->getClientFilename());
        self::assertSame('text/plain', $uploadedFile->getClientMediaType());
    }

    #[Test]
    public function testUriFactory(): void
    {
        $factory = new UriFactory();
        $uri = $factory->createUri('https://example.com');

        self::assertSame('https://example.com', (string) $uri);
    }
}
