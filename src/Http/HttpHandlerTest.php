<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Tests\Http;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\HttpServerStatus;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\SocketHttpServer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rayleigh\Config\ArrayConfig;
use Rayleigh\Http\HttpConfig;
use Rayleigh\Http\HttpHandler;

#[UsesClass(ArrayConfig::class)]
#[CoversClass(HttpHandler::class)]
#[CoversClass(HttpConfig::class)]
final class HttpHandlerTest extends TestCase
{
    private ?SocketHttpServer $server = null;

    protected function tearDown(): void
    {
        $this->server?->stop();
        $this->server = null;
    }

    #[Test]
    public function testHandleUseProxy(): void
    {
        $config = new ArrayConfig([
            'http.exposes' => '0.0.0.0:8080,0.0.0.0:8081',
            'http.useProxy' => 'true',
            'http.forwardedHeaderType' => 'forwarded',
            'http.trustedProxies' => '0.0.0.0/0',
            'http.enableCompression' => 'true',
            'http.connecitonLimit' => '1000',
            'http.connecitonLimitPerIp' => '10',
            'http.concurrencyLimit' => '1000',
            'http.allowedMethods' => 'GET,POST',
        ]);
        $handler = new HttpHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(RequestHandler::class),
            $this->createStub(ErrorHandler::class),
            new HttpConfig($config),
        );

        $this->server = $handler->listen();

        self::assertSame(HttpServerStatus::Started, $this->server->getStatus());
        self::assertSame(2, \count($this->server->getServers()));
        $server1 = $this->server->getServers()[0];
        self::assertSame('0.0.0.0:8080', $server1->getAddress()->toString());
        $server2 = $this->server->getServers()[1];
        self::assertSame('0.0.0.0:8081', $server2->getAddress()->toString());
    }

    #[Test]
    public function testHandleDontUseProxy(): void
    {
        $config = new ArrayConfig([
            'http.exposes' => '0.0.0.0:8082',
            'http.useProxy' => 'false',
            'http.forwardedHeaderType' => 'forwarded',
            'http.trustedProxies' => '0.0.0.0/0',
            'http.enableCompression' => 'true',
            'http.connecitonLimit' => '1000',
            'http.connecitonLimitPerIp' => '10',
            'http.concurrencyLimit' => '1000',
            'http.allowedMethods' => 'GET,POST',
        ]);
        $handler = new HttpHandler(
            $this->createStub(LoggerInterface::class),
            $this->createStub(RequestHandler::class),
            $this->createStub(ErrorHandler::class),
            new HttpConfig($config),
        );

        $this->server = $handler->listen();

        self::assertSame(HttpServerStatus::Started, $this->server->getStatus());
        self::assertSame(1, \count($this->server->getServers()));
        $server1 = $this->server->getServers()[0];
        self::assertSame('0.0.0.0:8082', $server1->getAddress()->toString());
    }
}
