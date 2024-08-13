<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpServer\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Rayleigh\HttpServer\ServerRequestRunner;

/**
 * Class ServerRequestRunnerTest
 * @package Rayleigh\HttpServer\Tests
 */
#[CoversClass(ServerRequestRunner::class)]
final class ServerRequestRunnerTest extends TestCase
{
    #[Test]
    public function testFailedHandleNoHandler(): void
    {
        $this->expectException(\RuntimeException::class);

        $runner = new ServerRequestRunner();

        $runner->handle($this->createStub(ServerRequestInterface::class));
    }
}
