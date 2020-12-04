<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use Closure;
use MakiseCo\ApplicationInterface;
use MakiseCo\Console\Traits\CommandTrait;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends SymfonyCommand
{
    use CommandTrait;

    protected ApplicationInterface $app;

    protected string $name = '';
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->setName($this->name);
        $this->setDescription($this->description);

        $this->defineArguments();
        $this->defineOptions();
    }

    protected function defineArguments(): void
    {
        foreach ($this->arguments as $argument) {
            $this->addArgument(...$argument);
        }
    }

    protected function defineOptions(): void
    {
        foreach ($this->options as $option) {
            $this->addOption(...$option);
        }
    }

    final public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $container = $this->app->getContainer();
        $closure = Closure::fromCallable([$this, 'handle']);

        return $container->call($closure) ?? 0;
    }

    /**
     * Returns services list that should be initialized before command starts and stopped after command finished
     *
     * @return string[]|null[] empty list means that the all services should be initialized/stopped,
     * [null] means that the no services will be initialized/stopped
     */
    public function getServices(): array
    {
        return [];
    }
}
