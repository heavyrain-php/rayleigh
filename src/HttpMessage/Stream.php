<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 Stream implementation
 * This class is mutable because resource stream can be edited in methods.
 * @package Rayleigh\HttpMessage
 * @final prefer to use decorator pattern when you extend this class
 * @link https://github.com/Nyholm/psr7/blob/master/doc/final.md
 */
final class Stream implements StreamInterface
{
    /**
     * Stream resource
     * It can be null if the stream is detached.
     * WARNING: It is mutable
     * @var resource|null
     */
    private $stream = null;

    /**
     * Cached stream metadata
     * WARNING: It is mutable
     * @link https://www.php.net/manual/en/function.stream-get-meta-data.php
     * @var array<string, mixed>
     */
    private array $metadata = [];

    /**
     * Cached stream size
     * WARNING: It is mutable
     * @var null|int
     */
    private ?int $cached_size = null;

    /**
     * Constructor
     * @param mixed $stream Streamable resource
     * @return void
     */
    public function __construct(mixed $stream)
    {
        if (\is_resource($stream)) {
            $this->stream = $stream;
            $this->metadata = \stream_get_meta_data($stream);
            return;
        } elseif (\is_string($stream) || \is_int($stream) || \is_float($stream) || \is_bool($stream)) {
            // WARNING: for huge string, it may cause memory overflow
            $resource = \fopen('php://memory', 'r+');
            if ($resource === false) {
                throw new \RuntimeException('Could not open php://memory'); // @codeCoverageIgnore
            }
            \fwrite($resource, \strval($stream));
            \fseek($resource, 0);
            $this->stream = $resource;
            $this->metadata = \stream_get_meta_data($resource);
            return;
        }
        throw new \InvalidArgumentException('Stream must be a resource or string');
    }

    /**
     * Always destruct the stream resource
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __toString(): string
    {
        // From PHP 7.0.4, __toString() can handle exceptions safely
        // @link https://bugs.php.net/bug.php?id=71485
        if ($this->isSeekable()) {
            $this->seek(0);
        }
        return $this->getContents();
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                @\fclose($this->stream);
            }
            $this->detach();
        }
    }

    public function detach(): mixed
    {
        if (!isset($this->stream)) {
            return null;
        }

        // reference
        $detachedStream = $this->stream;
        unset($this->stream);
        $this->metadata = [];
        $this->cached_size = null;
        return $detachedStream;
    }

    public function getSize(): ?int
    {
        // size may be cached
        if ($this->cached_size !== null) {
            return $this->cached_size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        /** @var string|null $uri */
        $uri = $this->metadata['uri'] ?? null;
        if ($uri !== null) {
            // clear realpath stat cache for calculating real size
            \clearstatcache(true, $uri);
        }

        $stats = \fstat($this->stream);
        if ($stats === false || !\array_key_exists('size', $stats)) {
            return null; // @codeCoverageIgnore
        }
        $this->cached_size = $stats['size'];
        return $this->cached_size;
    }

    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is already detached');
        }

        $result = @\ftell($this->stream);
        if ($result === false) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(\sprintf(
                'Could not get the position of the stream: %s',
                \error_get_last()['message'] ?? 'unknown error',
            ));
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }

    public function eof(): bool
    {
        if (!isset($this->stream)) {
            return true;
        }
        return @\feof($this->stream);
    }

    public function isSeekable(): bool
    {
        if ($this->metadata === []) {
            return false;
        }

        if (\array_key_exists('seekable', $this->metadata)) {
            return \boolval($this->metadata['seekable']);
        }

        return false;
    }

    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is already detached');
        }

        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (\fseek($this->stream, $offset, $whence) === -1) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(\sprintf(
                'Unable to seek to stream position "%d" with whence %d',
                $offset,
                $whence,
            ));
            // @codeCoverageIgnoreEnd
        }
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        if ($this->metadata === []) {
            return false;
        }

        if (!\array_key_exists('mode', $this->metadata)) {
            return false;
        }
        /** @var string $mode */
        $mode = $this->metadata['mode'];

        // @link https://www.php.net/manual/en/function.fopen.php
        return \array_key_exists($mode, [
            'r+' => true,
            'r+b' => true,
            'r+t' => true,
            'w' => true,
            'wb' => true,
            'wt' => true,
            'w+' => true,
            'w+b' => true,
            'w+t' => true,
            'rw' => true,
            'rwb' => true,
            'rwt' => true,
            'a' => true,
            'ab' => true,
            'at' => true,
            'a+' => true,
            'a+b' => true,
            'a+t' => true,
            'x' => true,
            'xb' => true,
            'xt' => true,
            'x+' => true,
            'x+b' => true,
            'x+t' => true,
            'c' => true,
            'cb' => true,
            'ct' => true,
            'c+' => true,
            'c+b' => true,
            'c+t' => true,
        ]);
    }

    public function write(string $string): int
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is already detached');
        }

        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }

        // write causes to change size
        $this->cached_size = null;

        $result = @\fwrite($this->stream, $string);
        if ($result === false) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(\sprintf(
                'Could not write to the stream: %s',
                \error_get_last()['message'] ?? 'unknown error',
            ));
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }

    public function isReadable(): bool
    {
        if ($this->metadata === []) {
            return false;
        }

        if (!\array_key_exists('mode', $this->metadata)) {
            return false;
        }
        /** @var string $mode */
        $mode = $this->metadata['mode'];

        // @link https://www.php.net/manual/en/function.fopen.php
        return \array_key_exists($mode, [
            'r' => true,
            'rb' => true,
            'rt' => true,
            'r+' => true,
            'r+b' => true,
            'r+t' => true,
            'w+' => true,
            'w+b' => true,
            'w+t' => true,
            'rw' => true,
            'rwb' => true,
            'rwt' => true,
            'a+' => true,
            'a+b' => true,
            'a+t' => true,
            'x+' => true,
            'x+b' => true,
            'x+t' => true,
            'c+' => true,
            'c+b' => true,
            'c+t' => true,
        ]);
    }

    public function read(int $length): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is already detached');
        }

        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        // @phpstan-ignore-next-line
        $result = @\fread($this->stream, $length);
        if ($result === false) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException(\sprintf(
                'Could not read from the stream: %s',
                \error_get_last()['message'] ?? 'unknown error',
            ));
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }

    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new \RuntimeException('Stream is already detached');
        }

        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        // safely try-get-contents
        /** @var \Throwable|null $exception */
        $exception = null;
        $error_handler = static function (int $errno, string $errstr) use (&$exception): bool {
            $exception = new \RuntimeException(\sprintf(
                'Unable to read stream contents: %d, %s',
                $errno,
                $errstr,
            ));
            return true; // stop propagation
        };

        \set_error_handler($error_handler);

        try {
            $contents = \stream_get_contents($this->stream);

            if ($contents !== false) {
                return $contents;
            }
        } catch (\Throwable $e) {
            $exception = $e;
        } finally {
            \restore_error_handler();
        }

        throw new \RuntimeException('Unable to read stream contents', 0, $exception);
    }

    public function getMetadata(?string $key = null): mixed
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        if ($key === null) {
            // copy
            $metadata = $this->metadata;
            return $metadata;
        }

        return \array_key_exists($key, $this->metadata) ? $this->metadata[$key] : null;
    }
}
