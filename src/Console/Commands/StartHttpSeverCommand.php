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
use MakiseCo\Http\Events\ServerStarted;
use MakiseCo\Http\HttpServer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StartHttpSeverCommand extends Command
{
    private EventDispatcher $dispatcher;
    private HttpServer $httpServer;
    private LoggerInterface $logger;
    private ConfigRepositoryInterface $config;

    public function __construct(
        EventDispatcher $dispatcher,
        HttpServer $httpServer,
        LoggerInterface $logger,
        ConfigRepositoryInterface $config
    ) {
        $this->dispatcher = $dispatcher;
        // set config before parent construct, because it will call pa
        $this->config = $config;

        parent::__construct(null);

        $this->httpServer = $httpServer;
        $this->logger = $logger;
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
            $this->config->get('http.host', '127.0.0.1')
        );

        $this->addOption(
            'port',
            'p',
            InputArgument::OPTIONAL,
            'Server port',
            $this->config->get('http.port', 10228)
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = $input->getOption('port');
        if (null !== $port) {
            $port = (int)$port;
        }
        $host = $input->getOption('host');

        $this->dispatcher->addListener(ServerStarted::class, function () use ($host, $port) {
            $this->logger->info('App is started', ['host' => $host, 'port' => $port]);
        });

        $this->httpServer->start($host, $port);

        $this->logger->info('App is stopped');

        return 0;
    }
}
