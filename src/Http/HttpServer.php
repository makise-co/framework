<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use Closure;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleServer;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function array_merge;
use function swoole_set_process_name;

class HttpServer
{
    public const MODE_MAIN = 'master';
    public const MODE_MANAGER = 'manager';
    public const MODE_WORKER = 'worker';

    protected string $mode = self::MODE_MAIN;

    protected SwooleServer $server;
    protected EventDispatcher $eventDispatcher;
    protected Swoole\SwoolePsrRequestFactoryInterface $requestFactory;
    protected Swoole\SwooleEmitter $emitter;
    protected RequestHandlerInterface $requestHandler;

    protected array $swooleConfig;
    protected string $appName;

    public function __construct(
        EventDispatcher $eventDispatcher,
        Swoole\SwoolePsrRequestFactoryInterface $requestFactory,
        Swoole\SwooleEmitter $emitter,
        RequestHandlerInterface $requestHandler,
        array $config,
        string $appName
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->requestFactory = $requestFactory;
        $this->emitter = $emitter;
        $this->requestHandler = $requestHandler;

        $this->swooleConfig = $config;
        $this->appName = $appName;
    }

    public function start(string $host, int $port): void
    {
        $this->server = new SwooleServer($host, $port);
        $this->server->set(
            array_merge(
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

        $this->server->on(
            'Start',
            function (SwooleServer $server) {
                $this->setProcessName('master process');

                $this->eventDispatcher->dispatch(new Events\ServerStarted());
            }
        );

        $this->server->on(
            'ManagerStart',
            function (SwooleServer $server) {
                $this->mode = self::MODE_MANAGER;

                $this->setProcessName('manager process');

                $this->eventDispatcher->dispatch(new Events\ManagerStarted());
            }
        );

        $this->server->on(
            'WorkerStart',
            function (SwooleServer $server, int $workerId) {
                $this->mode = self::MODE_WORKER;

                $this->setProcessName('worker process');

                $this->eventDispatcher->dispatch(new Events\WorkerStarted($workerId));
            }
        );

        $this->server->on(
            'WorkerStop',
            function (SwooleServer $server, int $workerId) {
                $this->mode = self::MODE_WORKER;

                $this->eventDispatcher->dispatch(new Events\WorkerStopped($workerId));
            }
        );

        $this->server->on(
            'WorkerExit',
            function (SwooleServer $server, int $workerId) {
                $this->mode = self::MODE_WORKER;

                $this->eventDispatcher->dispatch(new Events\WorkerExit($workerId));
            }
        );

        $this->server->on(
            'Shutdown',
            function (SwooleServer $server) {
                $this->eventDispatcher->dispatch(new Events\ServerShutdown());
            }
        );

        $this->server->on('Request', Closure::fromCallable([$this, 'onRequest']));

        $this->server->start();
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }

    protected function onRequest(Request $request, Response $response): void
    {
        $psrRequest = $this->requestFactory->create($request);

        $psrResponse = $this->requestHandler->handle($psrRequest);

        $this->emitter->emit($response, $psrResponse);
    }

    protected function setProcessName(string $name): void
    {
        if (!empty($this->appName)) {
            swoole_set_process_name("{$this->appName} {$name}");

            return;
        }

        swoole_set_process_name($name);
    }
}
