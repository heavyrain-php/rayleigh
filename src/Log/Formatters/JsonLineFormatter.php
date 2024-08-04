<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Formatters;

use Rayleigh\Log\FormatterInterface;
use Rayleigh\Log\Rfc5424LogLevel;

/**
 * JSON-Line formatter
 * @package Rayleigh\Log\Formatters
 */
class JsonLineFormatter implements FormatterInterface
{
    /**
     * Constructor
     * @param int $json_flags
     * @param string $line_endings
     * @return void
     */
    public function __construct(
        private readonly int $json_flags = \JSON_UNESCAPED_UNICODE,
        private readonly string $line_endings = \PHP_EOL,
    ) {
    }

    public function format(Rfc5424LogLevel $log_level, string $message, array $context): string
    {
        $ctx = [];

        $ctx['level'] = $log_level->toPsrLogLevel();
        $ctx['message'] = $message;
        $ctx['context'] = $context;

        $line = \json_encode($ctx, $this->json_flags) . $this->line_endings;

        $errmsg = \json_last_error_msg();
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            @\trigger_error(\sprintf("Failed to encode JSON: %s", $errmsg), \E_USER_WARNING);
            $line = \strtr($errmsg, "\r\n", "  ") . $this->line_endings;
        }

        return $line;
    }
}
