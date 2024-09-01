<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rayleigh\HttpMessage\UriPlaceholderResolver;
use Rayleigh\HttpServer\Route;
use Rayleigh\HttpServer\RouteNotFoundException;
use Rayleigh\HttpServer\RoutingHandler;
use Rayleigh\HttpServer\ServerRequestRunner;

/**
 * Class RoutingHandlerTest
 * @package Rayleigh\HttpServer\Tests
 */
#[CoversClass(RoutingHandler::class)]
#[CoversClass(Route::class)]
#[CoversClass(RouteNotFoundException::class)]
#[UsesClass(UriPlaceholderResolver::class)]
#[UsesClass(ServerRequestRunner::class)]
final class RoutingHandlerTest extends TestCase
{
    #[Test]
    public function testAddRoutes(): void
    {
        $handler = new RoutingHandler();

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubGet */
        $stubGet = $this->createStub(RequestHandlerInterface::class);
        $handler->get('/', $stubGet);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubPost */
        $stubPost = $this->createStub(RequestHandlerInterface::class);
        $handler->post('/', $stubPost);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubPut */
        $stubPut = $this->createStub(RequestHandlerInterface::class);
        $handler->put('/', $stubPut);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubDelete */
        $stubDelete = $this->createStub(RequestHandlerInterface::class);
        $handler->delete('/', $stubDelete);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubPatch */
        $stubPatch = $this->createStub(RequestHandlerInterface::class);
        $handler->patch('/', $stubPatch);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubHead */
        $stubHead = $this->createStub(RequestHandlerInterface::class);
        $handler->head('/', $stubHead);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubOptions */
        $stubOptions = $this->createStub(RequestHandlerInterface::class);
        $handler->options('*', $stubOptions);

        /** @var \PHPUnit\Framework\MockObject\Stub&RequestHandlerInterface $stubTrace */
        $stubTrace = $this->createStub(RequestHandlerInterface::class);
        $handler->trace('/', $stubTrace);

        $routes = $handler->getRoutes();

        self::assertCount(2, $routes);
        self::assertCount(7, $routes['/']);;
        self::assertCount(1, $routes['*']);
    }

    #[Test]
    public function testRouteNotFound(): void
    {
        $handler = new RoutingHandler();

        /** @var \PHPUnit\Framework\MockObject\MockObject&ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');
        /** @var \PHPUnit\Framework\MockObject\MockObject&UriInterface $uri */
        $uri = $this->createMock(UriInterface::class);
        $request->expects(self::once())
            ->method('getUri')
            ->willReturn($uri);
        $uri->expects(self::once())
            ->method('getPath')
            ->willReturn('/not_found');

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Route not found: GET /not_found');

        $handler->handle($request);
    }

    #[Test]
    public function testExactRoute(): void
    {
        $handler = new RoutingHandler();

        /** @var \PHPUnit\Framework\MockObject\MockObject&RequestHandlerInterface $stubGet */
        $stubGet = $this->createMock(RequestHandlerInterface::class);
        $handler->get('/users', $stubGet);
        $stubGet->expects(self::once())
            ->method('handle')
            ->willReturn($expected = $this->createStub(ResponseInterface::class));

        /** @var \PHPUnit\Framework\MockObject\MockObject&ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');
        /** @var \PHPUnit\Framework\MockObject\MockObject&UriInterface $uri */
        $uri = $this->createMock(UriInterface::class);
        $request->expects(self::once())
            ->method('getUri')
            ->willReturn($uri);
        $uri->expects(self::once())
            ->method('getPath')
            ->willReturn('/users');

        $response = $handler->handle($request);

        self::assertSame($expected, $response);
    }

    #[Test]
    public function testPlaceholderRoute(): void
    {
        $handler = new RoutingHandler();

        /** @var \PHPUnit\Framework\MockObject\MockObject&RequestHandlerInterface $stubPost */
        $stubPost = $this->createStub(RequestHandlerInterface::class);
        $handler->post('/posts', $stubPost);

        $mockGet = new class ($expected = $this->createMock(ResponseInterface::class)) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $expected)
            {
            }
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if ($request->getAttribute('user_id') !== '1') {
                    throw new \RuntimeException('Invalid user_id, expected \'1\', got \'' . $request->getAttribute('user_id') . '\'');
                }
                return $this->expected;
            }
        };
        $handler->get('/users/{user_id}', $mockGet);

        /** @var \PHPUnit\Framework\MockObject\MockObject&ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');
        $request->expects(self::once())
            ->method('withAttribute')
            ->with('user_id', '1')
            ->willReturn($request2 = $this->createMock(ServerRequestInterface::class));
        $request2->expects(self::once())
            ->method('getAttribute')
            ->with('user_id')
            ->willReturn('1');
        /** @var \PHPUnit\Framework\MockObject\MockObject&UriInterface $uri */
        $uri = $this->createMock(UriInterface::class);
        $request->expects(self::exactly(2))
            ->method('getUri')
            ->willReturn($uri);
        $uri->expects(self::exactly(2))
            ->method('getPath')
            ->willReturn('/users/1');

        $response = $handler->handle($request);

        self::assertSame($response, $expected);
    }

    #[Test]
    public function testWildcardRoute(): void
    {
        $handler = new RoutingHandler();

        /** @var \PHPUnit\Framework\MockObject\MockObject&RequestHandlerInterface $stubOptions */
        $stubOptions = $this->createMock(RequestHandlerInterface::class);
        $handler->options('*', $stubOptions);
        $stubOptions->expects(self::once())
            ->method('handle')
            ->willReturn($expected = $this->createStub(ResponseInterface::class));

        /** @var \PHPUnit\Framework\MockObject\MockObject&ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getMethod')
            ->willReturn('OPTIONS');
        /** @var \PHPUnit\Framework\MockObject\MockObject&UriInterface $uri */
        $uri = $this->createMock(UriInterface::class);
        $request->expects(self::once())
            ->method('getUri')
            ->willReturn($uri);
        $uri->expects(self::once())
            ->method('getPath')
            ->willReturn('/test');

        $response = $handler->handle($request);

        self::assertSame($response, $expected);
    }
}
