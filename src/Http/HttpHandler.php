<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Http;

use Amp\Http\Server\ErrorHandler;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\SocketHttpServer;
use Psr\Log\LoggerInterface;

/**
 * Handles HTTP connections.
 */
final readonly class HttpHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private RequestHandler $requestHandler,
        private ErrorHandler $errorHandler,
        private HttpConfig $config,
    ) {
    }

    /**
     * Starts to handle tcp connection.
     *
     * @return SocketHTTPServer
     */
    public function listen(): SocketHttpServer
    {
        $server = match ($this->config->useProxy()) {
            // create proxy-aware server
            true => SocketHttpServer::createForBehindProxy(
                $this->logger,
                $this->config->forwardedHeaderType(),
                $this->config->trustedProxies(),
                $this->config->enableCompression(),
                $this->config->concurrencyLimit(),
                $this->config->allowedMethods(),
            ),
            // create direct-access server
            false => SocketHttpServer::createForDirectAccess(
                $this->logger,
                $this->config->enableCompression(),
                $this->config->connecitonLimit(),
                $this->config->connecitonLimitPerIp(),
                $this->config->concurrencyLimit(),
                $this->config->allowedMethods(),
            ),
        };

        // exposes ip:port
        foreach ($this->config->exposes() as $expose) {
            $server->expose($expose);
        }

        // start to listen tcp socket connection
        $server->start($this->requestHandler, $this->errorHandler);

        return $server;
    }
}
