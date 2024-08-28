<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

/**
 * PSR-7 Message about Protocol Version like HTTP/1.1
 */
trait HasProtocolVersion
{
    protected string $protocol_version = '1.1'; // default

    /**
     * Get protocol version(e.g. HTTP/1.1 returns "1.1")
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol_version;
    }

    /**
     * With new Protocol version
     * @param string $version like "1.1"
     * @return static new instance
     */
    public function withProtocolVersion(string $version): static
    {
        $this->validateProtocolVersion($version);
        $new_instance = clone $this;
        $new_instance->protocol_version = $version;

        return $new_instance;
    }

    protected function validateProtocolVersion(string $version): void
    {
        if (!preg_match('/^\d\.\d$/', $version)) {
            throw new \InvalidArgumentException(\sprintf('Invalid protocol version, "%s" given', $version));
        }
    }
}
