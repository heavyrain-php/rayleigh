<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Message about Body Stream
 */
trait HasBody
{
    protected ?StreamInterface $stream = null;

    /**
     * Get body stream
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        if ($this->stream === null) {
            $this->stream = new Stream(''); // empty body
        }

        // returns stream instance reference
        return $this->stream;
    }

    /**
     * With new body stream
     * @param StreamInterface $stream
     * @return MessageInterface new instance
     */
    public function withBody(StreamInterface $stream): MessageInterface
    {
        $new_instance = clone $this;
        $new_instance->stream = $stream;

        return $new_instance;
    }
}
