<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Http;

use Amp\Http\Server\Middleware\ForwardedHeaderType;
use Rayleigh\Contracts\Config;

final readonly class HttpConfig
{
    private array $exposes;
    private bool $useProxy;
    private ForwardedHeaderType $forwardedHeaderType;
    private array $trustedProxies;
    private bool $enableCompression;
    private int $connecitonLimit;
    private int $connecitonLimitPerIp;
    private int $concurrencyLimit;
    private array $allowedMethods;

    public function __construct(Config $config)
    {
        $this->exposes = $config->getStringArray('http.exposes');
        $this->useProxy = $config->getBoolean('http.useProxy');
        $this->forwardedHeaderType = match ($config->getString('http.forwardedHeaderType')) {
            'forwarded' => ForwardedHeaderType::Forwarded,
            'x-forwarded-for' => ForwardedHeaderType::XForwardedFor,
            default => throw new \InvalidArgumentException('Invalid forwarded header type config provided value=' . $config->getString('http.forwardedHeaderType')),
        };
        $this->trustedProxies = $config->getStringArray('http.trustedProxies');
        $this->enableCompression = $config->getBoolean('http.enableCompression');
        $this->connecitonLimit = $config->getInteger('http.connecitonLimit');
        $this->connecitonLimitPerIp = $config->getInteger('http.connecitonLimitPerIp');
        $this->concurrencyLimit = $config->getInteger('http.concurrencyLimit');
        $this->allowedMethods = $config->getStringArray('http.allowedMethods');
    }

    public function exposes(): array
    {
        return $this->exposes;
    }

    public function useProxy(): bool
    {
        return $this->useProxy;
    }

    public function forwardedHeaderType(): ForwardedHeaderType
    {
        return $this->forwardedHeaderType;
    }

    public function trustedProxies(): array
    {
        return $this->trustedProxies;
    }

    public function enableCompression(): bool
    {
        return $this->enableCompression;
    }

    public function connecitonLimit(): int
    {
        return $this->connecitonLimit;
    }

    public function connecitonLimitPerIp(): int
    {
        return $this->connecitonLimitPerIp;
    }

    public function concurrencyLimit(): int
    {
        return $this->concurrencyLimit;
    }

    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
