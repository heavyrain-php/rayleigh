<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Container;

use Psr\Container\ContainerInterface;

/**
 * Container Aware Trait
 * @package Rayleigh\Container
 * @phpstan-require-implements ContainerAwareInterface
 */
trait ContainerAwareTrait
{
    /**
     * Container instance
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container = null;

    /**
     * Set container instance
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
