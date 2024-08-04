<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\HttpMessage\Stream;

/**
 * Class StreamTest
 * @package Rayleigh\HttpMessage\Tests
 */
#[CoversClass(Stream::class)]
final class StreamTest extends TestCase
{
    /**
     * @return iterable<string, array{0: mixed, 1: string}>
     */
    public static function getValidStreams(): iterable
    {
        yield 'empty string' => ['', ''];

        yield 'string' => ['abcテスト', 'abcテスト'];

        yield 'empty memory' => [\fopen('php://memory', 'r'), ''];

        yield 'empty temp' => [\fopen('php://temp', 'r'), ''];

        /** @var resource $memory */
        $memory = \fopen('php://memory', 'r+');
        \fwrite($memory, 'test');

        yield 'memory' => [$memory, 'test'];

        /** @var resource $memory2 */
        $memory2 = \fopen('php://memory', 'r+');
        \fwrite($memory2, 'test2');
        \fseek($memory2, 1);

        yield 'memory seeked' => [$memory2, 'test2'];

        yield 'int' => [1, '1'];

        yield 'float' => [3.5, '3.5'];

        yield 'bool true' => [true, '1'];
    }

    #[Test]
    #[DataProvider('getValidStreams')]
    public function testValidStreams(mixed $stream, string $expected): void
    {
        $instance = new Stream($stream);
        self::assertSame((string) $instance, $expected);
    }

    /**
     * @return iterable<string, array<array-key, mixed>>
     */
    public static function getInvalidStream(): iterable
    {
        yield 'empty' => [];

        yield 'null' => [null];

        /** @var resource $closed_stream */
        $closed_stream = \fopen('php://memory', 'r');
        \fclose($closed_stream);

        yield 'closed resource' => [$closed_stream];
    }

    #[Test]
    #[DataProvider('getInvalidStream')]
    public function testInvalidStreams(mixed $stream = null): void
    {
        $this->expectExceptionMessage('Stream must be a resource or string');

        new Stream($stream);
    }

    #[Test]
    public function testResourceWillBeClosedAfterFree(): void
    {
        $resource = \fopen('php://memory', 'r');

        $stream = new Stream($resource);
        unset($stream);

        self::assertFalse(\is_resource($resource));
    }

    #[Test]
    public function testResourceWillBeClosedAfterClose(): void
    {
        $resource = \fopen('php://memory', 'r');

        $stream = new Stream($resource);
        $stream->close();

        self::assertFalse(\is_resource($resource));
    }

    #[Test]
    public function testResourceDetach(): void
    {
        $resource = \fopen('php://memory', 'r');

        $stream = new Stream($resource);
        $detached_resource = $stream->detach();

        self::assertSame($resource, $detached_resource);

        self::assertNull($stream->detach());
    }

    #[Test]
    public function testGetsize(): void
    {
        $stream = new Stream('test');

        $actual = $stream->getSize();
        self::assertSame(4, $actual);

        // cached
        $actual2 = $stream->getSize();
        self::assertSame(4, $actual2);
    }

    #[Test]
    public function testGetFileSize(): void
    {
        $expected = \filesize(__FILE__);

        $resource = \fopen(__FILE__, 'r');
        $stream = new Stream($resource);

        self::assertSame($expected, $stream->getSize());
    }

    #[Test]
    public function testGetSizeOnClosedStream(): void
    {
        $stream = new Stream('');
        $stream->close();

        self::assertNull($stream->getSize());
    }

    #[Test]
    public function testSeek(): void
    {
        $stream = new Stream('testSeek');
        self::assertSame(0, $stream->tell());

        $stream->seek(1);
        self::assertSame(1, $stream->tell());
    }

    #[Test]
    public function testSeekFailed(): void
    {
        $resource = \fopen('php://stdout', 'r');
        \assert(false !== $resource);
        $stream = new Stream($resource);

        $this->expectExceptionMessage('Stream is not seekable');
        $stream->seek(1);
    }

    #[Test]
    public function testEof(): void
    {
        $stream = new Stream('testEof');
        self::assertFalse($stream->eof());

        $stream->close();
        self::assertTrue($stream->eof());
    }

    #[Test]
    public function testIsSeekable(): void
    {
        $stream = new Stream('testIsSeekable');
        self::assertTrue($stream->isSeekable());

        $stream->close();
        self::assertFalse($stream->isSeekable());
    }

    #[Test]
    public function testTellAlreadyDetached(): void
    {
        $stream = new Stream('');
        $stream->detach();

        $this->expectExceptionMessage('Stream is already detached');

        $stream->tell();
    }

    #[Test]
    public function testSeekAlreadyDetached(): void
    {
        $stream = new Stream('');
        $stream->detach();

        $this->expectExceptionMessage('Stream is already detached');

        $stream->seek(1);
    }

    #[Test]
    public function testIsWritable(): void
    {
        $stream = new Stream('testIsWritable');

        self::assertTrue($stream->isWritable());
    }

    #[Test]
    public function testWrite(): void
    {
        $stream = new Stream('testWrite');

        $stream->write('AAA');

        self::assertSame('AAAtWrite', (string) $stream);
    }

    #[Test]
    public function testWriteAlreadyDetached(): void
    {
        $stream = new Stream('');
        $stream->detach();

        $this->expectExceptionMessage('Stream is already detached');

        $stream->write('test');
    }

    #[Test]
    public function testWriteIsNotWritable(): void
    {
        $resource = \fopen('php://memory', 'r');
        \assert(false !== $resource);

        $stream = new Stream($resource);

        $this->expectExceptionMessage('Stream is not writable');

        $stream->write('test');
    }

    #[Test]
    public function testRead(): void
    {
        $stream = new Stream('ABCDEF');

        $expected = $stream->read(3);

        self::assertSame('ABC', $expected);
    }

    #[Test]
    public function testReadAlreadyDetached(): void
    {
        $stream = new Stream('');
        $stream->detach();

        $this->expectExceptionMessage('Stream is already detached');

        $stream->read(1);
    }

    #[Test]
    public function testReadIsNotReadable(): void
    {
        $resource = \fopen('php://stdout', 'x');
        \assert(false !== $resource);
        $stream = new Stream($resource);

        $this->expectExceptionMessage('Stream is not readable');

        $stream->read(1);
    }

    #[Test]
    public function testGetContentsAlreadyDetached(): void
    {
        $stream = new Stream('');
        $stream->detach();

        $this->expectExceptionMessage('Stream is already detached');

        $stream->getContents();
    }

    #[Test]
    public function testGetContentsHasError(): void
    {
        $resource = \fopen('php://stdout', 'x');
        \assert(false !== $resource);
        $stream = new Stream($resource);

        $this->expectExceptionMessage('Stream is not readable');

        $stream->getContents();
    }

    #[Test]
    public function testGetMetadata(): void
    {
        $resource = \fopen('php://stdin', 'r');
        \assert(false !== $resource);
        $stream = new Stream($resource);

        $expected = [
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
            'wrapper_type' => 'PHP',
            'stream_type' => 'STDIO',
            'mode' => 'r',
            'unread_bytes' => 0,
            'seekable' => false,
            'uri' => 'php://stdin',
        ];
        $actual = $stream->getMetadata();

        self::assertSame($expected, $actual);

        self::assertSame('PHP', $stream->getMetadata('wrapper_type'));

        $stream->close();

        self::assertNull($stream->getMetadata('wrapper_type'));
    }
}
