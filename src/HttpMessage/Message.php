<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\MessageInterface;

/**
 * PSR-7 Message implementation abstraction
 * @package Rayleigh\HttpMessage
 */
abstract class Message implements MessageInterface
{
    use HasBody;
    use HasHeaders;
    use HasProtocolVersion;

    /**
     * @param array<string, mixed> $headers
     * @return void
     */
    public function __construct(array $headers = [])
    {
        $this->header_bag = new HeaderBag($headers);
    }

    public function __clone()
    {
        // Clone instance properties
        $this->header_bag = clone $this->header_bag;
    }
}
