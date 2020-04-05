<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

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

    protected SwooleServer $server;
    protected HttpKernel $kernel;
    protected EventDispatcher $eventDispatcher;
    protected array $swooleConfig;
    protected string $appName;

    public function __construct(HttpKernel $kernel, EventDispatcher $eventDispatcher, array $config, string $appName)
    {
        $this->kernel = $kernel;
        $this->eventDispatcher = $eventDispatcher;
        $this->swooleConfig = $config;
        $this->appName = $appName;
    }

    public function start(string $host, int $port): void
    {
        $this->server = new SwooleServer($host, $port);
        $this->server->set(
            \array_merge(
                [
                    'daemonize' => false,
                    'worker_num' => 1,
                    'send_yield' => true,
                    'socket_type' => SWOOLE_SOCK_TCP,
                    'process_type' => SWOOLE_PROCESS,
                ],
                $this->swooleConfig
            )
        );

        $this->server->on('Start', function (SwooleServer $server) {
            $this->setProcessTitle('master process');

            $this->eventDispatcher->dispatch(new Events\ServerStarted());
        });

        $this->server->on('ManagerStart', function (SwooleServer $server) {
            $this->mode = self::MODE_MANAGER;

            $this->setProcessTitle('manager process');

            $this->eventDispatcher->dispatch(new Events\ManagerStarted());
        });

        $this->server->on('WorkerStart', function (SwooleServer $server, int $workerId) {
            $this->mode = self::MODE_WORKER;

            $this->setProcessTitle('worker process');

            $this->eventDispatcher->dispatch(new Events\WorkerStarted($workerId));
        });

        $this->server->on('WorkerStop', function (SwooleServer $server, int $workerId) {
            $this->mode = self::MODE_WORKER;

            $this->eventDispatcher->dispatch(new Events\WorkerStopped($workerId));
        });

        $this->server->on('WorkerExit', function (SwooleServer $server, int $workerId) {
            $this->mode = self::MODE_WORKER;

            $this->eventDispatcher->dispatch(new Events\WorkerExit($workerId));
        });

        $this->server->on('Shutdown', function (SwooleServer $server) {
            $this->eventDispatcher->dispatch(new Events\ServerShutdown());
        });

        $this->server->on('Request', function (Request $request, Response $response) {
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
        if (!empty($this->appName)) {
            \swoole_set_process_name("{$this->appName} {$title}");

            return;
        }

        \swoole_set_process_name($title);
    }
}
