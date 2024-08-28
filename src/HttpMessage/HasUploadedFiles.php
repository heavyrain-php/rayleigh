<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * PSR-7 UploadedFiles trait
 * @package Rayleigh\HttpMessage
 */
trait HasUploadedFiles
{
    /** @var array<string, mixed> $uploaded_files */
    protected array $uploaded_files = [];

    /**
     * Get uploaded files
     * @return array<array-key, mixed>
     */
    public function getUploadedFiles(): array
    {
        return $this->uploaded_files;
    }

    /**
     * With uploaded files
     * @param array<array-key, mixed> $uploadedFiles
     * @return ServerRequestInterface
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new_instance = clone $this;
        $new_instance->uploaded_files = [];
        $this->validateUploadedFiles($uploadedFiles);
        $new_instance->uploaded_files = $uploadedFiles;

        return $new_instance;
    }

    protected function validateUploadedFiles(mixed $uploaded_files): void
    {
        if (\is_array($uploaded_files)) {
            foreach ($uploaded_files as $uploaded_file) {
                $this->validateUploadedFiles($uploaded_file);
            }
        } elseif ($uploaded_files instanceof UploadedFileInterface === false) {
            throw new InvalidArgumentException('Invalid uploaded file');
        }
    }
}
