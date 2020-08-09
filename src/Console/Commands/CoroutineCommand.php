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
use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Runtime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function Swoole\Coroutine\run;

abstract class CoroutineCommand extends AbstractCommand
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        // command is invoked from another coroutine
        if (Coroutine::getCid() > 0) {
            return parent::run($input, $output);
        }

        $result = new CoroutineCommandResult();

        run(
            Closure::fromCallable([$this, 'execCoro']),
            $input,
            $output,
            $result
        );

        if (null !== $result->ex) {
            throw $result->ex;
        }

        return $result->result;
    }

    private function execCoro(InputInterface $input, OutputInterface $output, CoroutineCommandResult $result): void
    {
        Coroutine::defer(
            static function () {
                // do not block command coroutine exit if programmer have forgotten to release event loop
                if (Coroutine::stats()['event_num'] > 0) {
                    // force exit event loop
                    Event::exit();
                }
            }
        );

        // Make old synchronous calls - asynchronous
        Runtime::enableCoroutine();

        try {
            $result->result = parent::run($input, $output);
        } catch (Throwable $e) {
            $result->ex = $e;
        } finally {
            $this->app->terminate();
        }
    }
}
