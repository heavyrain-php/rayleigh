<?php

declare(strict_types=1);

/**
 * Class Uri
 * @package Rayleigh\HttpMessage
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\HttpMessage;

use InvalidArgumentException;

/**
 * Seriously malformed URI has provided
 * @package Rayleigh\HttpMessage
 */
class MalformedUriException extends InvalidArgumentException {}
