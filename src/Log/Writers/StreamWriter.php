<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Writers;

use Rayleigh\Log\FormatterInterface;
use Rayleigh\Log\Rfc5424LogLevel;
use Rayleigh\Log\WriterInterface;

/**
 * resource writer
 * @package Rayleigh\Log\Writers
 */
class StreamWriter implements WriterInterface
{
    /**
     * Constructor
     * @param resource $resource
     */
    public function __construct(
        protected readonly mixed $resource,
        protected readonly FormatterInterface $formatter,
    ) {
        if (!\is_resource($this->resource)) {
            throw new \InvalidArgumentException("Argument is not a resource");
        }
    }

    public function __destruct()
    {
        if (\is_resource($this->resource)) {
            @\fclose($this->resource);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write(Rfc5424LogLevel $log_level, string $message, array $context): void
    {
        if (!\is_resource($this->resource)) {
            throw new \RuntimeException("Resource is closed");
        }

        $line = $this->formatter->format($log_level, $message, $context);

        @\fwrite($this->resource, $line);
    }
}
