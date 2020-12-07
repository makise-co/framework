<?php
/*
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Console;

use DI\Container;
use MakiseCo\Application;
use MakiseCo\Console\Commands\AbstractCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class AbstractCommandTest extends TestCase
{
    public function testRun(): void
    {
        $command = $this->makeCommand();

        $consoleApp = new \Symfony\Component\Console\Application();
        $consoleApp->setAutoExit(false);
        $consoleApp->add($command);

        self::assertTrue($consoleApp->has('hello'));

        $output = $this->createMock(ConsoleOutput::class);
        $output
            ->expects(self::once())
            ->method('write')
            ->with('Hello, Okabe');

        $exitCode = $consoleApp->run(new ArrayInput(['command' => 'hello']), $output);

        self::assertSame(0, $exitCode);
    }

    private function makeCommand(): AbstractCommand
    {
        $appMock = $this->createMock(Application::class);
        $appMock
            ->method('getContainer')
            ->willReturn(new Container());

        $cmd = new class() extends AbstractCommand {
            protected string $name = 'hello';

            public function handle(): void
            {
                $this->write('Hello, Okabe');
            }
        };
        $cmd->setMakise($appMock);

        return $cmd;
    }
}
