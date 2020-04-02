<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\Http\HttpServer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartHttpSever extends Command
{
    private HttpServer $httpServer;
    private LoggerInterface $logger;

    public function __construct(HttpServer $httpServer, LoggerInterface $logger)
    {
        $this->httpServer = $httpServer;
        $this->logger = $logger;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->setName('http:start');
        $this->setDescription('Starts HTTP server');

        $this->addOption(
            'host',
            null,
            InputArgument::OPTIONAL,
            'Server host',
            '127.0.0.1'
        );

        $this->addOption(
            'port',
            'p',
            InputArgument::OPTIONAL,
            'Server port',
            '8000'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('App is started');

        $port = $input->getOption('port');
        if (null !== $port) {
            $port = (int)$port;
        }
        $host = $input->getOption('host');

        $this->httpServer->start($host, $port);

        $this->logger->info('App is stopped');

        return 0;
    }
}
