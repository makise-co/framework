<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use MakiseCo\Http\Handler\RequestHandler;
use MakiseCo\Http\Swoole\RequestFactory;
use MakiseCo\Http\Swoole\SwooleEmitter;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class HttpKernel
{
    protected RequestHandler $dispatcher;
    private RequestFactory $psr7Factory;
    private SwooleEmitter $swooleEmitter;

    public function __construct(RequestHandler $dispatcher, RequestFactory $psr7Factory, SwooleEmitter $swooleEmitter)
    {
        $this->dispatcher = $dispatcher;
        $this->psr7Factory = $psr7Factory;
        $this->swooleEmitter = $swooleEmitter;
    }

    public function handle(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $psr7Request = $this->psr7Factory->createFromSwoole($swooleRequest);

        $psr7Response = $this->dispatcher->handle($psr7Request);

        $this->swooleEmitter->emit($swooleResponse, $psr7Response);
    }
}
