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
use Swoole\Coroutine;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventDispatcher;

class StartHttpSeverCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('http:start');
        $this->setDescription('Starts HTTP server');

        $config = $this->app->getContainer()->get(ConfigRepositoryInterface::class);

        $this->addOption(
            'host',
            null,
            InputOption::VALUE_OPTIONAL,
            'Server host',
            $config->get('http.host', '127.0.0.1')
        );

        $this->addOption(
            'port',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Server port',
            $config->get('http.port', 10228)
        );
    }

    public function handle(EventDispatcher $dispatcher, LoggerInterface $logger, HttpServer $server): int
    {
        if (Coroutine::getCid() > 0) {
            $this->error("Please run {$this->getName()} command with \"--no-coroutine\" flag");

            return 1;
        }

        $port = $this->getOption('port');
        if (null !== $port) {
            $port = (int)$port;
        }
        $host = $this->getOption('host');

        $dispatcher->addListener(
            ServerStarted::class,
            static function () use ($host, $port, $logger) {
                $logger->info('App is started', ['host' => $host, 'port' => $port]);
            }
        );

        $server->start($host, $port);

        $logger->info('App is stopped');

        return 0;
    }
}
