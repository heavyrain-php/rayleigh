<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Tests;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Rayleigh\HttpMessage\Stream;
use Rayleigh\HttpMessage\UploadedFile;

/**
 * Class UploadedFileTest
 * @package Rayleigh\HttpMessage
 */
#[CoversClass(UploadedFile::class)]
#[CoversClass(Stream::class)]
final class UploadedFileTest extends TestCase
{
    /** @var string[] $temp_file_name_list */
    private static $temp_file_name_list = [];

    #[AfterClass]
    public static function tearDownAfterClass(): void
    {
        foreach (self::$temp_file_name_list as $temp_file_name) {
            @\unlink($temp_file_name);
        }
        self::$temp_file_name_list = [];
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function getValidInputs(): iterable
    {
        $temp_file_resource = \tmpfile();
        \assert($temp_file_resource !== false);

        yield 'Temp file resource' => [$temp_file_resource];

        yield 'int' => [8];

        yield 'float' => [3.5];

        yield 'bool true' => [true];

        $temp_file_name = \tempnam(\sys_get_temp_dir(), 'rayleigh_test');
        \assert($temp_file_name !== false);
        \file_put_contents($temp_file_name, 'test');
        self::$temp_file_name_list[] = $temp_file_name;

        yield 'Temp file name' => [$temp_file_name];

        yield 'StreamInterface' => [new Stream('test')];
    }

    #[Test]
    #[DataProvider('getValidInputs')]
    public function testValidInputs(mixed $input): void
    {
        $uploadedFile = new UploadedFile($input, null, \UPLOAD_ERR_OK, 'client.txt', 'plain/text');
        self::assertInstanceOf(UploadedFile::class, $uploadedFile);
        self::assertInstanceOf(StreamInterface::class, $uploadedFile->getStream());
        self::assertSame('client.txt', $uploadedFile->getClientFilename());
        self::assertSame('plain/text', $uploadedFile->getClientMediaType());
    }

    #[Test]
    public function testInvalidErrorCode(): void
    {
        $this->expectExceptionMessage('Invalid error code');

        new UploadedFile(null, null, 1000);
    }

    #[Test]
    public function testErrorFile(): void
    {
        $uploadedFile = new UploadedFile(null, null, \UPLOAD_ERR_NO_FILE);
        self::assertInstanceOf(UploadedFile::class, $uploadedFile);
        self::assertSame(null, $uploadedFile->getSize());
        self::assertSame(\UPLOAD_ERR_NO_FILE, $uploadedFile->getError());
        self::assertSame('No file was uploaded', $uploadedFile->getErrorMessage());
        self::assertSame(null, $uploadedFile->getClientFilename());
        self::assertSame(null, $uploadedFile->getClientMediaType());
    }

    /**
     * @return array<string, array{0: int, 1: string}>
     */
    public static function getErrorMessages(): array
    {
        return [
            'OK' => [\UPLOAD_ERR_OK, 'There is no error, the file uploaded with success.'],
            'INI_SIZE' => [\UPLOAD_ERR_INI_SIZE, 'The uploaded file exceeds the upload_max_filesize directive in php.ini'],
            'FORM_SIZE' => [\UPLOAD_ERR_FORM_SIZE, 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'],
            'PARTIAL' => [\UPLOAD_ERR_PARTIAL, 'The uploaded file was only partially uploaded'],
            'NO_FILE' => [\UPLOAD_ERR_NO_FILE, 'No file was uploaded'],
            'NO_TMP_DIR' => [\UPLOAD_ERR_NO_TMP_DIR, 'Missing a temporary folder'],
            'CANT_WRITE' => [\UPLOAD_ERR_CANT_WRITE, 'Failed to write file to disk'],
            'EXTENSION' => [\UPLOAD_ERR_EXTENSION, 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.'],
        ];
    }

    #[Test]
    #[DataProvider('getErrorMessages')]
    public function testGetErrorMessages(int $error, string $expected): void
    {
        $uploadedFile = new UploadedFile('', null, $error);

        self::assertSame($expected, $uploadedFile->getErrorMessage());
    }

    #[Test]
    public function testMoveToEmptyPath(): void
    {
        $this->expectExceptionMessage('Invalid path provided for move operation; must be a non-empty string');

        $uploadedFile = new UploadedFile('', null, \UPLOAD_ERR_OK);
        $uploadedFile->moveTo('');
    }

    #[Test]
    public function testMoveToErrorFile(): void
    {
        $this->expectExceptionMessage('Cannot retrieve stream due to upload error');

        $uploadedFile = new UploadedFile('', null, \UPLOAD_ERR_NO_FILE);
        $uploadedFile->moveTo(\sys_get_temp_dir());
    }

    #[Test]
    public function testMoveToFilePath(): void
    {
        $temp_file_name = \tempnam(\sys_get_temp_dir(), 'rayleigh_test');
        \assert($temp_file_name !== false);
        self::$temp_file_name_list[] = $temp_file_name;

        $uploadedFile = new UploadedFile($temp_file_name, null, \UPLOAD_ERR_OK);
        $uploadedFile->moveTo(\sys_get_temp_dir() . '/moved.txt');

        self::assertFileExists(\sys_get_temp_dir() . '/moved.txt');
        self::$temp_file_name_list[] = \sys_get_temp_dir() . '/moved.txt';
    }

    #[Test]
    public function testMoveToStream(): void
    {
        $stream = new Stream('test');

        $uploadedFile = new UploadedFile($stream, 4, \UPLOAD_ERR_OK);
        $uploadedFile->moveTo(\sys_get_temp_dir() . '/moved2.txt');

        self::assertFileExists(\sys_get_temp_dir() . '/moved2.txt');
        self::assertFileMatchesFormat('test', \sys_get_temp_dir() . '/moved2.txt');
        self::$temp_file_name_list[] = \sys_get_temp_dir() . '/moved2.txt';
    }

    #[Test]
    public function testMoveToDoesNotExist(): void
    {
        $this->expectExceptionMessage('Cannot open targetPath: /does/not/exist/moved3.txt');

        $stream = new Stream('a');
        $uploadedFile = new UploadedFile($stream, 1, \UPLOAD_ERR_OK);
        $uploadedFile->moveTo('/does/not/exist/moved3.txt');
    }

    #[Test]
    public function testMoveToAlreadyMoved(): void
    {
        $this->expectExceptionMessage('Cannot retrieve stream after it has already been moved');

        $stream = new Stream('b');
        $uploadedFile = new UploadedFile($stream, null, \UPLOAD_ERR_OK);
        $uploadedFile->moveTo(\sys_get_temp_dir() . '/moved4.txt');

        self::assertFileExists(\sys_get_temp_dir() . '/moved4.txt');
        self::$temp_file_name_list[] = \sys_get_temp_dir() . '/moved4.txt';

        $uploadedFile->moveTo(\sys_get_temp_dir() . '/moved5.txt');
    }
}
