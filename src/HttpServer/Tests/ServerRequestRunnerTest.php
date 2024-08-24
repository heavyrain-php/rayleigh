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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rayleigh\HttpServer\ServerRequestRunner;

/**
 * Class ServerRequestRunnerTest
 * @package Rayleigh\HttpServer\Tests
 */
#[CoversClass(ServerRequestRunner::class)]
final class ServerRequestRunnerTest extends TestCase
{
    #[Test]
    public function testEmptyMiddleware(): void
    {
        /** @var ResponseInterface $response */
        $response = self::createStub(ResponseInterface::class);
        /** @var \PHPUnit\Framework\MockObject\MockObject&RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->willReturn($response);
        $runner = new ServerRequestRunner([], $handler);

        /** @var ServerRequestInterface $request */
        $request = self::createStub(ServerRequestInterface::class);

        $actual = $runner->handle($request);

        self::assertSame($response, $actual);
    }

    #[Test]
    public function testMiddlewareStackLastInFirstOut(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&ServerRequestInterface $request1 */
        $request1 = $this->createMock(ServerRequestInterface::class);
        $request1->expects(self::once())
            ->method('withAttribute')
            ->with('test2', 'test2')
            ->willReturn($request2 = $this->createMock(ServerRequestInterface::class));
        $request2->expects(self::once())
            ->method('withAttribute')
            ->with('test1', 'test1')
            ->willReturn($this->createMock(ServerRequestInterface::class));

        $response1 = $this->createMock(ResponseInterface::class);
        $response1->expects(self::once())
            ->method('withAddedHeader')
            ->with('X-Test', 'Test1')
            ->willReturn($response2 = $this->createMock(ResponseInterface::class));

        $response2->expects(self::once())
            ->method('withAddedHeader')
            ->with('X-Test', 'Test2')
            ->willReturn($response3 = $this->createMock(ResponseInterface::class));

        $middleware1 = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request->withAttribute('test1', 'test1'));
                return $response->withAddedHeader('X-Test', 'Test1');
            }
        };
        $middleware2 = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request->withAttribute('test2', 'test2'));
                return $response->withAddedHeader('X-Test', 'Test2');
            }
        };

        /** @var \PHPUnit\Framework\MockObject\MockObject&RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->willReturn($response1);
        $runner = new ServerRequestRunner([$middleware1, $middleware2], $handler);

        $actual = $runner->handle($request1);

        self::assertSame($response3, $actual);
    }

    #[Test]
    public function testInvalidMiddleware(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Middleware must be an instance of ' . MiddlewareInterface::class);

        $response = self::createStub(ResponseInterface::class);
        $handler = new class ($response) implements RequestHandlerInterface {
            public function __construct(private readonly ResponseInterface $response) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        new ServerRequestRunner(['foo'], $handler);
    }
}
