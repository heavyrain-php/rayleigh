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
    /** @var array<array-key, UploadedFileInterface> $uploaded_files */
    protected array $uploaded_files = [];

    /**
     * Get uploaded files
     * @return array<array-key, UploadedFileInterface>
     */
    public function getUploadedFiles(): array
    {
        return $this->uploaded_files;
    }

    /**
     * With uploaded files
     * @param array<array-key, UploadedFileInterface> $uploaded_files
     * @return ServerRequestInterface
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploaded_files): ServerRequestInterface
    {
        $new_instance = clone $this;
        $new_instance->uploaded_files = [];
        foreach ($uploaded_files as $key => $uploaded_file) {
            if (!$uploaded_file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid uploaded file');
            }
            $new_instance->uploaded_files[$key] = $uploaded_file;
        }

        return $new_instance;
    }
}
