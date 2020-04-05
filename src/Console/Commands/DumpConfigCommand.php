<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\Config\ConfigRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpConfigCommand extends Command
{
    protected ConfigRepositoryInterface $config;

    public function __construct(ConfigRepositoryInterface $config)
    {
        $this->config = $config;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->setName('config:dump');
        $this->addArgument(
            'path',
            InputArgument::OPTIONAL,
            'Specific config path',
            null
        );

        $this->setDescription('Show app configuration');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');

        if (null === $path) {
            dump($this->config->toArray());
        } else {
            dump($this->config->get($path));
        }

        return 0;
    }
}
