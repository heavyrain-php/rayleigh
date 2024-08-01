<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * PSR-7 UploadedFile implementation
 * @package Rayleigh\HttpMessage
 */
class UploadedFile implements UploadedFileInterface
{
    private const VALID_ERROR_CODES = [
        \UPLOAD_ERR_OK => true,
        \UPLOAD_ERR_INI_SIZE => false,
        \UPLOAD_ERR_FORM_SIZE => false,
        \UPLOAD_ERR_PARTIAL => false,
        \UPLOAD_ERR_NO_FILE => false,
        \UPLOAD_ERR_NO_TMP_DIR => false,
        \UPLOAD_ERR_CANT_WRITE => false,
        \UPLOAD_ERR_EXTENSION => false,
    ];

    /**
     * Uploaded file stream
     * Set null when upload error
     * Set resource or string when it is file
     * @var mixed
     */
    private readonly mixed $input;

    /**
     * Uploaded file size
     * @var int|null
     */
    private readonly ?int $size;

    /**
     * Uploaded Error type
     * @link https://www.php.net/manual/en/features.file-upload.errors.php
     * @var int
     */
    private readonly int $error;

    /**
     * Client file name when provided
     * @var null|string
     */
    private readonly ?string $client_filename;

    /**
     * Client media type when provided
     * @var null|string
     */
    private readonly ?string $client_media_type;

    /**
     * Whether the uploaded file has already been moved
     * @var bool
     */
    private bool $was_moved = false;

    /**
     * Create a new uploaded file object
     * @param mixed $input
     * @param null|int $size
     * @param int $error
     * @param null|string $client_filename
     * @param null|string $client_media_type
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct(
        mixed $input,
        ?int $size = null,
        int $error = \UPLOAD_ERR_OK,
        ?string $client_filename = null,
        ?string $client_media_type = null,
    ) {
        if (!\array_key_exists($error, self::VALID_ERROR_CODES)) {
            throw new InvalidArgumentException('Invalid error code');
        }
        $this->input = $this->getStreamFromMixed($input, $error);
        $this->error = $error;
        $this->size = $size;
        $this->client_filename = $client_filename;
        $this->client_media_type = $client_media_type;
    }

    /**
     * Create a Stream from mixed input
     * null when input is null or error
     * @param mixed $input
     * @param int $error
     * @return mixed
     */
    private function getStreamFromMixed(mixed $input, int $error): mixed
    {
        if ($error !== \UPLOAD_ERR_OK) {
            return null;
        }
        if ($input instanceof StreamInterface) {
            return $input;
        }
        if (\is_string($input)) {
            // filePath
            if (\PHP_SAPI !== 'cli' && \is_uploaded_file($input) === false) {
                throw new RuntimeException('Invalid uploaded file'); // @codeCoverageIgnore
            }
            return $input;
        }
        if (\is_resource($input) || \is_int($input) || \is_float($input) || \is_bool($input)) {
            return new Stream($input);
        }
        throw new InvalidArgumentException('Invalid input provided:' . \gettype($input)); // @codeCoverageIgnore
    }

    private function validateValidStream(): void
    {
        if ($this->error !== \UPLOAD_ERR_OK || $this->input === null) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }
        if ($this->was_moved) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    public function getStream(): StreamInterface
    {
        $this->validateValidStream();

        if ($this->input instanceof StreamInterface) {
            return $this->input;
        }

        return new Stream($this->input);
    }

    public function moveTo(string $targetPath): void
    {
        if ($targetPath === '') {
            throw new InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string');
        }

        $this->validateValidStream();

        if (\is_string($this->input)) {
            if (\PHP_SAPI !== 'cli' && \is_uploaded_file($this->input) === false) {
                throw new RuntimeException('Invalid uploaded file'); // @codeCoverageIgnore
            }

            // filePath
            $this->was_moved = \PHP_SAPI === 'cli' ?
                \rename($this->input, $targetPath) :
                \move_uploaded_file($this->input, $targetPath); // @codeCoverageIgnore

            if ($this->was_moved === false) {
                throw new RuntimeException('Error occurred while moving uploaded file'); // @codeCoverageIgnore
            }
            return;
        }

        // resource or StreamInterface
        $stream = $this->getStream();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $resource = @\fopen($targetPath, 'w');
        if ($resource === false) {
            throw new RuntimeException('Cannot open targetPath: ' . $targetPath);
        }
        try {
            while (!$stream->eof()) {
                if (@\fwrite($resource, $stream->read(1024 * 1024)) === false) {
                    throw new RuntimeException('Error occurred while writing to new file'); // @codeCoverageIgnore
                }
            }
        } finally {
            \fclose($resource);
        }
        $this->was_moved = true;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Get error message from error code
     * @link https://www.php.net/manual/en/features.file-upload.errors.php
     * @return string
     */
    public function getErrorMessage(): string
    {
        return match ($this->error) {
            \UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
            \UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            \UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            \UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            \UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            \UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.',
            default => 'Unknown error', // @codeCoverageIgnore
        };
    }

    public function getClientFilename(): ?string
    {
        return $this->client_filename;
    }

    public function getClientMediaType(): ?string
    {
        return $this->client_media_type;
    }
}
