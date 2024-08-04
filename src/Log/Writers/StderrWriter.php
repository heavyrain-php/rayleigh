<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Log\Writers;

use Rayleigh\Log\FormatterInterface;

/**
 * php://stderr writer
 * @package Rayleigh\Log\Writers
 */
/* readonly */ class StderrWriter extends StreamWriter
{
    /**
     * Constructor
     * @param FormatterInterface $formatter
     */
    public function __construct(
        FormatterInterface $formatter,
    ) {
        $resource = \fopen('php://stderr', 'w');
        \assert(\is_resource($resource));
        parent::__construct($resource, $formatter);
    }
}
