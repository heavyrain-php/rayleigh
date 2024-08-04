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
use Rayleigh\HttpMessage\HasStatusCode;
use Rayleigh\HttpMessage\HeaderBag;
use Rayleigh\HttpMessage\Response;
use Rayleigh\HttpMessage\Stream;

/**
 * Class RequestTest
 * @package Rayleigh\HttpMessage\Tests
 */
#[CoversClass(HeaderBag::class)]
#[CoversClass(Response::class)]
#[CoversClass(Stream::class)]
#[CoversTrait(HasStatusCode::class)]
final class ResponseTest extends TestCase
{
    #[Test]
    public function testHasStatusCode(): void
    {
        $response = new Response();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('OK', $response->getReasonPhrase());

        $new_response = $response->withStatus(404);

        self::assertNotSame($response, $new_response);
        self::assertSame(404, $new_response->getStatusCode());
        self::assertSame('Not Found', $new_response->getReasonPhrase());

        $new_response2 = $new_response->withStatus(500, 'Unknown error');

        self::assertNotSame($new_response, $new_response2);
        self::assertSame(500, $new_response2->getStatusCode());
        self::assertSame('Unknown error', $new_response2->getReasonPhrase());

        $new_response3 = new Response(200, [], '{"message": "Hello, World!"}', '1.1', 'Alright');

        self::assertSame('{"message": "Hello, World!"}', (string) $new_response3->getBody());
        self::assertSame('Alright', $new_response3->getReasonPhrase());
    }

    #[Test]
    public function testInvalidStatusCode(): void
    {
        $this->expectExceptionMessage('Status code must be an integer between 100 and 599');

        new Response(99);
    }

    #[Test]
    public function testInvalidStatusCode2(): void
    {
        $response = new Response();

        $this->expectExceptionMessage('Status code must be an integer between 100 and 599');

        $response->withStatus(600);
    }
}
