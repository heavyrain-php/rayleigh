<?php

declare(strict_types=1);

/**
 * @author Masaru Yamagishi <akai_inu@live.jp>
 * @license Apache-2.0
 */

namespace Rayleigh\Console;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Rayleigh\Console\Commands\ListCommand;
use Rayleigh\Console\Input\ArgvInput;
use Rayleigh\Console\Output\ArrayOutput;
use Rayleigh\Container\ContainerAwareInterface;
use Rayleigh\Container\ContainerAwareTrait;
use ReflectionClass;

/**
 * Console Kernel
 * @package Rayleigh\Console
 * @link https://github.com/symfony/symfony/blob/7.2/src/Symfony/Component/Console/Application.php
 * example:
 * ```php
 * $kernel = new Kernel([
 *     SomeCommand::class,
 * ]);
 * $kernel->setLogger($logger);
 * $kernel->setContainer($container);
 * $kernel->addCommand(new OtherCommand());
 * $kernel->run();
 * ```
 */
class Kernel implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /** @var string[] $command_classnames */
    private array $command_classnames = [];

    /** @var array<string, CommandInterface> $command_instances */
    private array $command_instances = [];

    /**
     * Kernel constructor
     * @param (string|CommandInterface)[] $commands
     * @return void
     */
    public function __construct(array $commands = [])
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }
    }

    /**
     * Add command to console
     * @param string|CommandInterface $command
     * @return void
     */
    public function addCommand(string|CommandInterface $command): void
    {
        if (\is_string($command)) {
            if (!\class_exists($command)) {
                throw new \InvalidArgumentException(\sprintf('Command class "%s" not found', $command));
            }
            $this->command_classnames[] = $command;
        } else {
            $command_name = $command->getCommandName();
            if (\array_key_exists($command_name, $this->command_instances)) {
                throw new \InvalidArgumentException(\sprintf('Command "%s" already exists', $command_name));
            }
            $this->command_instances[$command_name] = $command;
        }
    }

    /**
     * Run console command
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int exit code, 0 is success
     */
    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $input ??= new ArgvInput();
        $output ??= new ArrayOutput();
        $commands = $this->getInstanciatedCommands();
        $list_command = new ListCommand($commands);

        $command_name = $input->getCommandName();
        if (\is_null($command_name)) {
            // Show command list and exit
            return $list_command->execute($input, $output);
        }
        if (!\array_key_exists($command_name, $commands)) {
            throw new \RuntimeException(\sprintf('Command "%s" not found', $command_name));
        }
        $command = $commands[$command_name];

        return $command->execute($input, $output);
    }

    /**
     * Get instanciated commands
     * @return CommandInterface[]
     */
    private function getInstanciatedCommands(): array
    {
        /** @var class-string $classname */
        foreach ($this->command_classnames as $classname) {
            if ($this->container?->has($classname)) {
                $command = $this->container->get($classname);
                \assert($command instanceof CommandInterface, 'Command must be instance of CommandInterface');
                $this->addCommand($command);
            } else {
                $ref = new ReflectionClass($classname);
                if ($ref->isInstantiable() && $ref->implementsInterface(CommandInterface::class)) {
                    $command = $ref->newInstance();
                    \assert($command instanceof CommandInterface, 'Command must be instance of CommandInterface');
                    $this->addCommand($command);
                } else {
                    throw new CommandCannotBeInstanciatedException($classname);
                }
            }
        }

        return $this->command_instances;
    }
}
