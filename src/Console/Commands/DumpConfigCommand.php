<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\Config\AppConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpConfigCommand extends Command
{
    protected AppConfigInterface $config;

    public function __construct(AppConfigInterface $config)
    {
        $this->config = $config;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->setName('config:dump');

        $this->setDescription('Show app configuration');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dump = \print_r($this->config, true);

        $output->writeln("<info>{$dump}</info>");

        return 0;
    }
}
