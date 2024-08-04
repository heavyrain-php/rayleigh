<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rayleigh\HttpMessage\HeaderBag;

/**
 * Class HeaderBagTest
 * @package Rayleigh\HttpMessage\Tests
 */
#[CoversClass(HeaderBag::class)]
final class HeaderBagTest extends TestCase
{
    #[Test]
    public function testEmptyConstructor(): void
    {
        $bag = new HeaderBag();

        self::assertSame([], $bag->all());
    }

    /**
     * @return array<string, array{0: string, 1: mixed, 2: array<string, string[]>}>
     */
    public static function getValidHeaders(): array
    {
        return [
            'host' => ['Host', 'example.com', ['host' => ['example.com']]],
            'host2' => ['hOsT', 'example2.com', ['host' => ['example2.com']]],
            'date' => ['Date', 'Tue, 4 Sep 2012 20:00:00 +0200', ['date' => ['Tue, 4 Sep 2012 20:00:00 +0200']]],
            'scalar' => ['a', 1, ['a' => ['1']]],
            'scalar2' => ['b', 2.5, ['b' => ['2.5']]],
            'scalar3' => ['c', true, ['c' => ['1']]],
            'has whitespace' => ['Content-Type', "\t\t   application/json  ", ['content-type' => ["application/json"]]],
            'Japanese' => ['Japanese', 'テスト', ['japanese' => ['テスト']]],
        ];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array<string, string[]> $expected
     */
    #[Test]
    #[DataProvider('getValidHeaders')]
    public function testGetValidHeaders(string $name, mixed $value, array $expected): void
    {
        $bag = new HeaderBag([
            $name => $value,
        ]);

        self::assertTrue($bag->has($name));
        self::assertSame($expected, $bag->all());
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function getInvalidHeaders(): array
    {
        return [
            'empty' => [''],
            'null' => [null],
            'empty array' => [[]],
            'object' => [new \stdClass()],
            'crlf' => ["application/json\r\n"],
        ];
    }

    #[Test]
    #[DataProvider('getInvalidHeaders')]
    public function testGetInvalidHeaders(mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HeaderBag([
            'invalid' => $value,
        ]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function getInvalidHeaderNames(): array
    {
        return [
            'empty' => [''],
            'Japanese' => ['日本語'],
            'slash' => ['///'],
            'has whitespace' => ['Content-Type   '],
        ];
    }

    #[Test]
    #[DataProvider('getInvalidHeaderNames')]
    public function testInvalidHeaderNames(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HeaderBag([
            $name => 'value',
        ]);
    }

    #[Test]
    public function testMutation(): void
    {
        $bag = new HeaderBag([
            'initial' => 'value',
        ]);

        self::assertTrue($bag->has('initial'));
        self::assertSame(['value'], $bag->get('initial'));

        $bag->add('initial', 'value2');

        self::assertTrue($bag->has('initial'));
        self::assertSame(['value', 'value2'], $bag->get('initial'));

        $bag->replace('initial', 'value3');

        self::assertTrue($bag->has('initial'));
        self::assertSame(['value3'], $bag->get('initial'));

        $bag->remove('initial');

        self::assertFalse($bag->has('initial'));
        self::assertSame([], $bag->get('initial'));
    }

    #[Test]
    public function testCloning(): void
    {
        $bag = new HeaderBag([
            'initial' => 'value',
        ]);

        $bag2 = clone $bag;

        $bag->remove('initial');

        self::assertFalse($bag->has('initial'));
        self::assertTrue($bag2->has('initial'));
    }
}
