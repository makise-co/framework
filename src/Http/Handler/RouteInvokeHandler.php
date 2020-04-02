<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteInvokeHandler implements RequestHandlerInterface
{
    private ControllerInvoker $invoker;

    public function __construct(ControllerInvoker $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * @param ServerRequestInterface|\MakiseCo\Http\Request $request
     * @return ResponseInterface
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $request->getRoute()->getHandler()->getClosure();
        $args = $request->attributes->get('args', []);

        return $this->invoker->invoke($handler, $args, $request);
    }
}
