<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container;

use Psr\Container\ContainerInterface;

/**
 * Container Aware interface
 * @package Rayleigh\Container
 */
interface ContainerAwareInterface
{
    /**
     * Set container instance
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void;
}
