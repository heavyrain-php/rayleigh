<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Rayleigh\HttpServer\TraditionalServerRequestBuilder;

#[CoversClass(TraditionalServerRequestBuilder::class)]
#[UsesTrait(\Rayleigh\HttpMessage\HasAttributes::class)]
#[UsesTrait(\Rayleigh\HttpMessage\HasMethod::class)]
#[UsesTrait(\Rayleigh\HttpMessage\HasParams::class)]
#[UsesTrait(\Rayleigh\HttpMessage\HasParsedBody::class)]
#[UsesTrait(\Rayleigh\HttpMessage\HasProtocolVersion::class)]
#[UsesTrait(\Rayleigh\HttpMessage\HasUploadedFiles::class)]
#[UsesClass(\Rayleigh\HttpMessage\HeaderBag::class)]
#[UsesClass(\Rayleigh\HttpMessage\Internal\UriPartsParser::class)]
#[UsesClass(\Rayleigh\HttpMessage\Message::class)]
#[UsesClass(\Rayleigh\HttpMessage\Request::class)]
#[UsesClass(\Rayleigh\HttpMessage\ServerRequest::class)]
#[UsesClass(\Rayleigh\HttpMessage\UploadedFile::class)]
#[UsesClass(\Rayleigh\HttpMessage\Uri::class)]
final class TraditionalServerRequestBuilderTest extends TestCase
{
    #[Test]
    public function testBuiltinServer(): void
    {
        $SERVER = [
            'DOCUMENT_ROOT' => '/app/rayleigh',
            'REMOTE_ADDR' => '127.0.0.1',
            'REMOTE_PORT' => '52586',
            'SERVER_SOFTWARE' => 'PHP/8.3.10 (Development Server)',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => '0.0.0.0',
            'SERVER_PORT' => '8080',
            'REQUEST_URI' => '/test.php?test=foo',
            'REQUEST_METHOD' => 'GET',
            'SCRIPT_NAME' => '/test.php',
            'SCRIPT_FILENAME' => '/app/rayleigh/test.php',
            'PHP_SELF' => '/test.php',
            'QUERY_STRING' => 'test=foo',
            'HTTP_HOST' => 'localhost:8080',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_SEC_CH_UA' => '"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            'HTTP_SEC_CH_UA_MOBILE' => '?0',
            'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'HTTP_SEC_FETCH_SITE' => 'none',
            'HTTP_SEC_FETCH_MODE' => 'navigate',
            'HTTP_SEC_FETCH_USER' => '?1',
            'HTTP_SEC_FETCH_DEST' => 'document',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br, zstd',
            'HTTP_ACCEPT_LANGUAGE' => 'ja,en-US;q=0.9,en;q=0.8',
            'HTTP_COOKIE' => 'PHPSESSID=ra3rpdd1aba6tglm2q70mbb29f',
            'REQUEST_TIME_FLOAT' => 1724641506.684002,
            'REQUEST_TIME' => 1724641506,
        ];
        $COOKIE = [
            'PHPSESSID' => 'ra3rpdd1aba6tglm2q70mbb29f',
        ];
        $FILES = [];
        $GET = [
            'test' => 'foo',
        ];
        $POST = [];
        $REQUEST = [
            'test' => 'foo',
        ];

        $request = TraditionalServerRequestBuilder::build($SERVER, $COOKIE, $FILES, $GET, $POST, $REQUEST);

        self::assertInstanceOf(ServerRequestInterface::class, $request);
        self::assertSame('GET', $request->getMethod());
        self::assertSame(['PHPSESSID' => 'ra3rpdd1aba6tglm2q70mbb29f'], $request->getCookieParams());
        self::assertSame([], $request->getUploadedFiles());
        self::assertNull($request->getParsedBody());
        self::assertSame(['test' => 'foo'], $request->getQueryParams());
        self::assertSame('1.1', $request->getProtocolVersion());
        self::assertSame([], $request->getAttributes());
    }

    public function testApache(): void
    {
        $SERVER = [
            'HTTP_HOST' => 'localhost:8080',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_SEC_CH_UA' => '"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            'HTTP_SEC_CH_UA_MOBILE' => '?0',
            'HTTP_SEC_CH_UA_PLATFORM' => '"Windows"',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'HTTP_SEC_FETCH_SITE' => 'none',
            'HTTP_SEC_FETCH_MODE' => 'navigate',
            'HTTP_SEC_FETCH_USER' => '?1',
            'HTTP_SEC_FETCH_DEST' => 'document',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br, zstd',
            'HTTP_ACCEPT_LANGUAGE' => 'ja,en-US;q=0.9,en;q=0.8',
            'HTTP_COOKIE' => 'PHPSESSID=ra3rpdd1aba6tglm2q70mbb29f',
            'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            'SERVER_SIGNATURE' => '
Apache/2.4.61 (Debian) Server at localhost Port 8080

',
            'SERVER_SOFTWARE' => 'Apache/2.4.61 (Debian)',
            'SERVER_NAME' => 'localhost',
            'SERVER_ADDR' => '172.17.0.2',
            'SERVER_PORT' => '8080',
            'REMOTE_ADDR' => '172.17.0.1',
            'DOCUMENT_ROOT' => '/var/www/html',
            'REQUEST_SCHEME' => 'http',
            'CONTEXT_PREFIX' => '',
            'CONTEXT_DOCUMENT_ROOT' => '/var/www/html',
            'SERVER_ADMIN' => 'webmaster@localhost',
            'SCRIPT_FILENAME' => '/var/www/html/test.php',
            'REMOTE_PORT' => '43044',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'test=foo',
            'REQUEST_URI' => '/test.php?test=foo',
            'SCRIPT_NAME' => '/test.php',
            'PHP_SELF' => '/test.php',
            'REQUEST_TIME_FLOAT' => 1724654454.427512,
            'REQUEST_TIME' => 1724654454,
            'argv' => [
                'test=foo',
            ],
            'argc' => 1,
        ];
        $COOKIE = [
            'PHPSESSID' => 'ra3rpdd1aba6tglm2q70mbb29f',
        ];
        $FILES = [];
        $GET = [
            'test' => 'foo',
        ];
        $POST = [];
        $REQUEST = [
            'test' => 'foo',
            'PHPSESSID' => 'ra3rpdd1aba6tglm2q70mbb29f',
        ];

        $request = TraditionalServerRequestBuilder::build($SERVER, $COOKIE, $FILES, $GET, $POST, $REQUEST);

        self::assertInstanceOf(ServerRequestInterface::class, $request);
        self::assertSame('GET', $request->getMethod());
        self::assertSame(['PHPSESSID' => 'ra3rpdd1aba6tglm2q70mbb29f'], $request->getCookieParams());
        self::assertSame([], $request->getUploadedFiles());
        self::assertNull($request->getParsedBody());
        self::assertSame(['test' => 'foo'], $request->getQueryParams());
        self::assertSame('1.1', $request->getProtocolVersion());
        self::assertSame([], $request->getAttributes());
    }

    /**
     * @return iterable<string, array{0: array<non-empty-string, string>, 1: array<non-empty-string, string>, 2: string, 3: ?string}>
     */
    public static function getBuildMethodList(): iterable
    {
        // [$server, $post, $expected]
        yield 'GET' => [['REQUEST_METHOD' => 'GET'], [], 'GET', null];

        yield 'POST' => [['REQUEST_METHOD' => 'POST'], [], 'POST', null];

        yield 'PUT' => [['REQUEST_METHOD' => 'PUT'], [], 'PUT', null];

        yield 'PUT_OVERRIDE' => [['REQUEST_METHOD' => 'POST', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT'], [], 'POST', 'PUT'];

        yield 'PUT_BUT_GET' => [['REQUEST_METHOD' => 'GET', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT'], [], 'GET', 'PUT'];

        yield 'DELETE_FORM' => [['REQUEST_METHOD' => 'POST'], ['_method' => 'DELETE'], 'POST', 'DELETE'];

        yield 'ALL' => [['REQUEST_METHOD' => 'POST', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH'], ['_method' => 'DELETE'], 'POST', 'PATCH'];

        yield 'UNDEFINED' => [['REQUEST_METHOD' => 'UNDEFINED'], [], 'UNDEFINED', null];

        yield 'CASE_SENSITIVE' => [['REQUEST_METHOD' => 'POST'], ['_method' => 'dELeTe'], 'POST', null];
    }

    /**
     * @param array<non-empty-string, string> $server
     * @param array<non-empty-string, string> $post
     * @param string $expected
     * @return void
     */
    #[Test]
    #[DataProvider('getBuildMethodList')]
    public function testBuildMethod(array $server, array $post, string $expected): void
    {
        $builder = self::getExtendedBuilder(
            $server,
            [],
            [],
            [],
            $post,
            $post,
        );

        $actual = $builder->buildMethod();

        self::assertSame($expected, $actual);
    }

    /**
     * @param array<non-empty-string, string> $server
     * @param array<non-empty-string, string> $post
     * @param string $_
     * @param ?string $expected
     * @return void
     */
    #[Test]
    #[DataProvider('getBuildMethodList')]
    public function testBuildOverrideMethodPost(array $server, array $post, string $_, ?string $expected): void
    {
        $builder = self::getExtendedBuilder(
            $server,
            [],
            [],
            [],
            $post,
            $post,
        );

        $actual = $builder->buildOverrideMethod();

        self::assertSame($expected, $actual);
    }

    /**
     * @param array<non-empty-string, string> $server
     * @param array<non-empty-string, string> $get
     * @param string $_
     * @param ?string $expected
     * @return void
     */
    #[Test]
    #[DataProvider('getBuildMethodList')]
    public function testBuildOverrideMethodGet(array $server, array $get, string $_, ?string $expected): void
    {
        $builder = self::getExtendedBuilder(
            $server,
            [],
            [],
            $get,
            [],
            $get,
        );

        $actual = $builder->buildOverrideMethod();

        self::assertSame($expected, $actual);
    }

    /**
     * @return iterable<string, array{0: array<non-empty-string, string>, 1: string}>
     */
    public static function getServerProtocolList(): iterable
    {
        yield '0.9' => [['SERVER_PROTOCOL' => 'HTTP/0.9'], '0.9'];

        yield '1.0' => [['SERVER_PROTOCOL' => 'HTTP/1.0'], '1.0'];

        yield '1.1' => [['SERVER_PROTOCOL' => 'HTTP/1.1'], '1.1'];

        yield '2.0' => [['SERVER_PROTOCOL' => 'HTTP/2.0'], '2.0'];

        yield '3.0' => [['SERVER_PROTOCOL' => 'HTTP/3.0'], '3.0'];
    }

    /**
     * @param array<non-empty-string, string> $server
     * @param string $expected
     */
    #[Test]
    #[DataProvider('getServerProtocolList')]
    public function testBuildProtocolVersion(array $server, string $expected): void
    {
        $builder = self::getExtendedBuilder(
            $server,
        );

        $actual = $builder->buildProtocolVersion();

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function testBuildProtocolVersionUnknown(): void
    {
        $builder = self::getExtendedBuilder(
            ['SERVER_PROTOCOL' => 'FTP/1.0'],
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown server protocol: FTP/1.0');

        $builder->buildProtocolVersion();
    }

    #[Test]
    public function testBuildHeaders(): void
    {
        $server = [
            'HTTP_HOST' => 'localhost:8080',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_SEC_CH_UA' => '"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"',
            'CONTENT_TYPE' => 'plain/html',
            'CONTENT_LENGTH' => '1',
            'CONTENT_MD5' => 'd41d8cd98f00b204e9800998ecf8427e',
        ];
        $builder = self::getExtendedBuilder(
            $server,
        );

        $actual = $builder->buildHeaders();

        self::assertSame([
            'Content-Type' => ['plain/html'],
            'Content-Length' => ['1'],
            'Content-Md5' => ['d41d8cd98f00b204e9800998ecf8427e'],
            'Host' => ['localhost:8080'],
            'Connection' => ['keep-alive'],
            'Sec-Ch-Ua' => ['"Chromium";v="128", "Not;A=Brand";v="24", "Google Chrome";v="128"'],
        ], $actual);
    }

    #[Test]
    public function testBuildUploadedFiles(): void
    {
        $files = [
            'avatar' => [
                'tmp_name' => 'phpUxcOty',
                'name' => 'my-avatar.png',
                'size' => 90996,
                'type' => 'image/png',
                'error' => 0,
            ],
        ];

        $builder = self::getExtendedBuilder(files: $files);

        $actual = $builder->buildUploadedFiles();

        self::assertCount(1, $actual);
        self::assertArrayHasKey('avatar', $actual);
        self::assertInstanceOf(UploadedFileInterface::class, $actual['avatar']);
    }

    #[Test]
    public function testBuildUploadedFilesRecursively2(): void
    {
        $files = [
            'my-form' => [
                'name' => [
                    'details' => [
                        'avatars' => [
                            0 => 'my-avatar.png',
                            1 => 'my-avatar2.png',
                            2 => 'my-avatar3.png',
                        ],
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatars' => [
                            0 => 'image/png',
                            1 => 'image/png',
                            2 => 'image/png',
                        ],
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatars' => [
                            0 => 'phpmFLrzD',
                            1 => 'phpV2pBil',
                            2 => 'php8RUG8v',
                        ],
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatars' => [
                            0 => 0,
                            1 => 0,
                            2 => 0,
                        ],
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatars' => [
                            0 => 90996,
                            1 => 90996,
                            2 => 90996,
                        ],
                    ],
                ],
            ],
        ];

        $builder = self::getExtendedBuilder(files: $files);

        $actual = $builder->buildUploadedFiles();

        self::assertArrayHasKey('my-form', $actual);
        self::assertIsArray($actual['my-form']);
        self::assertArrayHasKey('details', $actual['my-form']);
        self::assertIsArray($actual['my-form']['details']);
        self::assertArrayHasKey('avatars', $actual['my-form']['details']);
        self::assertCount(3, $actual['my-form']['details']['avatars']);
        self::assertInstanceOf(UploadedFileInterface::class, $actual['my-form']['details']['avatars'][0]);
        self::assertInstanceOf(UploadedFileInterface::class, $actual['my-form']['details']['avatars'][1]);
        self::assertInstanceOf(UploadedFileInterface::class, $actual['my-form']['details']['avatars'][2]);
    }

    #[Test]
    public function testBuildUploadedFilesRecursively(): void
    {
        $files = [
            'my-form' => [
                'name' => [
                    'details' => [
                        'avatar' => 'my-avatar.png',
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatar' => 'image/png',
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatar' => 'phpmFLrzD',
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatar' => 0,
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatar' => 90996,
                    ],
                ],
            ],
        ];

        $builder = self::getExtendedBuilder(files: $files);

        $actual = $builder->buildUploadedFiles();

        self::assertArrayHasKey('my-form', $actual);
        self::assertIsArray($actual['my-form']);
        self::assertArrayHasKey('details', $actual['my-form']);
        self::assertIsArray($actual['my-form']['details']);
        self::assertArrayHasKey('avatar', $actual['my-form']['details']);
        self::assertInstanceOf(UploadedFileInterface::class, $actual['my-form']['details']['avatar']);
    }

    /**
     * @param array<non-empty-string, mixed> $server
     * @param array<non-empty-string, mixed> $cookie
     * @param array<non-empty-string, mixed> $files
     * @param array<non-empty-string, mixed> $get
     * @param array<non-empty-string, mixed> $post
     * @param array<non-empty-string, mixed> $request
     */
    private static function getExtendedBuilder(
        array $server = [],
        array $cookie = [],
        array $files = [],
        array $get = [],
        array $post = [],
        array $request = [],
    ): TraditionalServerRequestBuilder
    {
        // use anonymous class for public constructor
        return new class ($server, $cookie, $files, $get, $post, $request) extends TraditionalServerRequestBuilder {
            /**
             * @param array<non-empty-string, mixed> $server
             * @param array<non-empty-string, mixed> $cookie
             * @param array<non-empty-string, mixed> $files
             * @param array<non-empty-string, mixed> $get
             * @param array<non-empty-string, mixed> $post
             * @param array<non-empty-string, mixed> $request
             */
            public function __construct(
                array $server,
                array $cookie,
                array $files,
                array $get,
                array $post,
                array $request,
            ){
                parent::__construct($server, $cookie, $files, $get, $post, $request);
            }
        };
    }
}
