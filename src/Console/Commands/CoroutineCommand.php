<?php
/**
 * File: Command.php
 * Author: Dmitry K. <dmitry.k@brainex.co>
 * Date: 2020-03-09
 * Copyright (c) 2020
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\ApplicationInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class CoroutineCommand extends SymfonyCommand
{
    protected ApplicationInterface $app;

    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;

        parent::__construct(null);
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $result = 0;
        /* @var \Throwable $ex */
        $ex = null;

        \Swoole\Coroutine\run(function (InputInterface $input, OutputInterface $output) use (&$result, &$ex) {
            // Make old synchronous calls - asynchronous
            \Swoole\Runtime::enableCoroutine();

            try {
                $result = parent::run($input, $output);
            } catch (\Throwable $e) {
                $ex = $e;
            } finally {
                $this->app->terminate();
            }
        }, $input, $output);

        if (null !== $ex) {
            throw $ex;
        }

        return $result;
    }
}
