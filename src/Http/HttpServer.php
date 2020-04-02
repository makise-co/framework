<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use MakiseCo\Config\AppConfigInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleServer;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpServer
{
    public const MODE_MAIN = 'master';
    public const MODE_MANAGER = 'manager';
    public const MODE_WORKER = 'worker';

    protected string $mode = self::MODE_MAIN;

    protected AppConfigInterface $appConfig;
    protected SwooleServer $server;
    protected HttpKernel $kernel;
    protected EventDispatcher $eventDispatcher;

    public function __construct(AppConfigInterface $appConfig, HttpKernel $kernel, EventDispatcher $eventDispatcher)
    {
        $this->appConfig = $appConfig;
        $this->kernel = $kernel;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function start(string $host, int $port): void
    {
        $this->server = new SwooleServer($host, $port);
        $this->server->set(
            [
                'daemonize' => false,
                'worker_num' => $this->appConfig->getHttpConfig()->getWorkerNum(),
                'send_yield' => true,
                'socket_type' => SWOOLE_SOCK_TCP,
                'process_type' => SWOOLE_PROCESS,
            ]
        );

        $this->server->on('start', function (SwooleServer $server) {
            $this->setProcessTitle('master process');

            $this->eventDispatcher->dispatch(new Events\ServerStarted());
        });

        $this->server->on('managerStart', function (SwooleServer $server) {
            $this->mode = self::MODE_MANAGER;

            $this->setProcessTitle('manager process');

            $this->eventDispatcher->dispatch(new Events\ManagerStarted());
        });

        $this->server->on('workerStart', function (SwooleServer $server) {
            $this->mode = self::MODE_WORKER;

            $this->setProcessTitle('worker process');

            $this->eventDispatcher->dispatch(new Events\WorkerStarted());
        });

        $this->server->on('shutdown', function (SwooleServer $server) {
            $this->eventDispatcher->dispatch(new Events\ServerShutdown());
        });

        $this->server->on('request', function (Request $request, Response $response) {
            $this->kernel->handle($request, $response);
        });

        $this->server->start();
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }

    public function getKernel(): HttpKernel
    {
        return $this->kernel;
    }

    protected function setProcessTitle(string $title): void
    {
        $appName = $this->appConfig->getName();
        if (!empty($appName)) {
            \swoole_set_process_name("{$appName} {$title}");

            return;
        }

        \swoole_set_process_name($title);
    }
}
