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
use Rayleigh\HttpMessage\Message;
use Rayleigh\HttpMessage\Stream;

/**
 * Class MessageTest
 * @package Rayleigh\HttpMessage
 */
#[CoversClass(Message::class)]
#[CoversClass(Stream::class)]
#[CoversClass(HeaderBag::class)]
final class MessageTest extends TestCase
{
    #[Test]
    public function testProtocolVersion(): void
    {
        $message = new class extends Message
        {
        };

        self::assertSame('1.1', $message->getProtocolVersion());

        $message2 = $message->withProtocolVersion('2.0');

        self::assertNotSame($message2, $message);
        self::assertSame('1.1', $message->getProtocolVersion());
        self::assertSame('2.0', $message2->getProtocolVersion());
    }

    #[Test]
    public function testBody(): void
    {
        $message = new class extends Message
        {
        };

        self::assertSame('', (string) $message->getBody());

        $message2 = $message->withBody(new Stream('test'));

        self::assertNotSame($message2, $message);
        self::assertNotSame($message2->getBody(), $message->getBody());
        self::assertSame('test', (string) $message2->getBody());
    }

    #[Test]
    public function testHeaders(): void
    {
        $message = new class extends Message
        {
        };

        self::assertSame([], $message->getHeaders());
        self::assertFalse($message->hasHeader('Host'));
        self::assertSame([], $message->getHeader('Host'));
        self::assertSame('', $message->getHeaderLine('Host'));

        $message2 = $message->withHeader('Host', 'example.com');

        self::assertNotSame($message2, $message);
        self::assertSame(['host' => ['example.com']], $message2->getHeaders());
        self::assertTrue($message2->hasHeader('Host'));
        self::assertSame(['example.com'], $message2->getHeader('Host'));
        self::assertSame('example.com', $message2->getHeaderLine('Host'));

        $message3 = $message2->withAddedHeader('Accept', '*');

        self::assertNotSame($message3, $message2);
        self::assertSame(['host' => ['example.com'], 'accept' => ['*']], $message3->getHeaders());
        self::assertTrue($message3->hasHeader('Accept'));
        self::assertSame(['*'], $message3->getHeader('Accept'));
        self::assertSame('*', $message3->getHeaderLine('Accept'));

        $message4 = $message3->withoutHeader('Host');

        self::assertNotSame($message4, $message3);
        self::assertFalse($message4->hasHeader('Host'));

        $message5 = $message4->withAddedHeader('Accept', 'application/json; charset=utf-8');

        self::assertNotSame($message5, $message4);
        self::assertSame(['accept' => ['*', 'application/json; charset=utf-8']], $message5->getHeaders());
        self::assertSame('*, application/json; charset=utf-8', $message5->getHeaderLine('Accept'));
    }
}
