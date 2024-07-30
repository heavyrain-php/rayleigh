<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Class UriPartsParserTest
 * @package Rayleigh\HttpMessage\Internal
 */
#[CoversClass(UriPartsParser::class)]
final class UriPartsParserTest extends TestCase
{
    #[Test]
    public function testParseFromArray(): void
    {
        $parts = [
            'scheme' => 0,
            'user' => 1,
            'pass' => 2,
            'host' => 3,
            'port' => null,
            'path' => 4,
            'query' => 5,
            'fragment' => 6,
        ];

        $expected = [
            'scheme' => '',
            'user' => '',
            'pass' => '',
            'host' => '',
            'port' => null,
            'path' => '',
            'query' => '',
            'fragment' => '',
        ];

        self::assertSame($expected, UriPartsParser::parseFromArray($parts));
    }

    #[Test]
    public function testParseIpv6(): void
    {
        $uri = 'https://[2001:db8::7]:8443';
        $expected = [
            'scheme' => 'https',
            'user' => '',
            'pass' => '',
            'host' => '[2001:db8::7]',
            'port' => 8443,
            'path' => '',
            'query' => '',
            'fragment' => '',
        ];

        self::assertSame($expected, UriPartsParser::parseFromString($uri));
    }

    #[Test]
    public function testInvalidPort(): void
    {
        $this->expectExceptionMessage('Invalid port: -1. Must be between 0 and 65535');

        UriPartsParser::parseFromArray(['port' => '-1']);
    }
}
