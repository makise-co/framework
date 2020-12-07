<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Application;

use MakiseCo\Console\Commands\AbstractCommand;

class SomeCommand extends AbstractCommand
{
    public function configure(): void
    {
        $this->setName('some');
    }

    public function handle(): int
    {
        return 2;
    }
}
