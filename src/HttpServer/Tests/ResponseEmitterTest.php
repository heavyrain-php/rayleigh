<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Rayleigh\HttpServer\Emitter;
use Rayleigh\HttpServer\ResponseEmitter;

/**
 * Class ResponseEmitterTest
 * @package Rayleigh\HttpServer\Tests
 */
#[CoversClass(ResponseEmitter::class)]
final class ResponseEmitterTest extends TestCase
{
    #[Test]
    public function testEmit(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&Emitter $emitter */
        $emitter = $this->createMock(Emitter::class);
        $emitter->expects(self::once())
            ->method('hasSentHeader')
            ->willReturn(false);
        $emitter->expects(self::once())
            ->method('hasObFlushed')
            ->willReturn(false);
        $emitter->expects($invoked_count = self::exactly(4))
            ->method('emitHeader')
            // withConsecutive fallback implementation
            ->with(
                self::callback(static function (string $name) use ($invoked_count): bool {
                    return match ($invoked_count->numberOfInvocations()) {
                        1 => $name === 'Content-Type',
                        2 => $name === 'Content-Length',
                        3 => $name === 'Set-Cookie',
                        4 => $name === 'Set-Cookie',
                        default => false,
                    };
                }),
                self::callback(static function (string $value) use ($invoked_count): bool {
                    return match ($invoked_count->numberOfInvocations()) {
                        1 => $value === 'text/plain',
                        2 => $value === '11',
                        3 => $value === 'test1=1',
                        4 => $value === 'test2=2',
                        default => false,
                    };
                }),
                self::callback(static function (bool $replace) use ($invoked_count): bool {
                    return match ($invoked_count->numberOfInvocations()) {
                        1 => $replace === true,
                        2 => $replace === true,
                        3 => $replace === false,
                        4 => $replace === false,
                        default => false,
                    };
                }),
                self::callback(static function (int $code) use ($invoked_count): bool {
                    return match ($invoked_count->numberOfInvocations()) {
                        1 => $code === 200,
                        2 => $code === 200,
                        3 => $code === 200,
                        4 => $code === 200,
                        default => false,
                    };
                }),
            );

        $emitter->expects(self::once())
            ->method('emitStatusLine')
            ->with('1.1', 200, 'OK');

        /** @var \PHPUnit\Framework\MockObject\Stub&StreamInterface $stream */
        $stream = $this->createStub(StreamInterface::class);
        $emitter->expects(self::once())
            ->method('emitBody')
            ->with($stream);

        /** @var \PHPUnit\Framework\MockObject\MockObject&ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $response->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);
        $response->expects(self::once())
            ->method('getHeaders')
            ->willReturn([
                'Content-Type' => ['text/plain'],
                'Content-Length' => ['11'],
                'Set-Cookie' => ['test1=1', 'test2=2'],
            ]);
        $response->expects(self::once())
            ->method('getProtocolVersion')
            ->willReturn('1.1');
        $response->expects(self::once())
            ->method('getReasonPhrase')
            ->willReturn('OK');
        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($stream);

        $response_emitter = new ResponseEmitter($emitter);

        $response_emitter->emit($response);
    }

    #[Test]
    public function testTerminate(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&Emitter $emitter */
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects(self::once())
            ->method('terminateResponse');

        $response_emitter = new ResponseEmitter($emitter);

        $response_emitter->terminate();
    }

    #[Test]
    public function testHasSentHeader(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&Emitter $emitter */
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects(self::once())
            ->method('hasSentHeader')
            ->willReturn(true);

        $response_emitter = new ResponseEmitter($emitter);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Header has already been sent');

        /** @var \PHPUnit\Framework\MockObject\Stub&ResponseInterface $response */
        $response = $this->createStub(ResponseInterface::class);
        $response_emitter->emit($response);
    }

    #[Test]
    public function testHasObFlushed(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&Emitter $emitter */
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects(self::once())
            ->method('hasSentHeader')
            ->willReturn(false);
        $emitter->expects(self::once())
            ->method('hasObFlushed')
            ->willReturn(true);

        $response_emitter = new ResponseEmitter($emitter);

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Output buffer has already been flushed');

        /** @var \PHPUnit\Framework\MockObject\Stub&ResponseInterface $response */
        $response = $this->createStub(ResponseInterface::class);
        $response_emitter->emit($response);
    }
}
