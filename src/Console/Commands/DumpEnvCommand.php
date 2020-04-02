<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpEnvCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('env:dump');

        $this->setDescription('Show loaded environment variables');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($_ENV as $key => $value) {
            $output->writeln("<comment>{$key}</comment>=<info>{$value}</info>");
        }

        return 0;
    }
}
