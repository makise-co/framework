<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SomeCommand extends Command
{
    public function configure(): void
    {
        $this->setName('some');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
