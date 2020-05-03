<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Handler;

use MakiseCo\Http\Middleware\Exception\MethodNotAllowedException;
use MakiseCo\Http\Middleware\Exception\RouteNotFoundException;
use MakiseCo\Http\Router\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDispatchHandler implements RequestHandlerInterface
{
    private \FastRoute\Dispatcher $dispatcher;
    private ControllerInvoker $invoker;

    public function __construct(\FastRoute\Dispatcher $dispatcher, ControllerInvoker $invoker)
    {
        $this->dispatcher = $dispatcher;
        $this->invoker = $invoker;
    }

    /**
     * @param ServerRequestInterface|\MakiseCo\Http\Request $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $routeInfo = $this->dispatcher->dispatch($request->getMethod(), $request->getRequestTarget());

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new RouteNotFoundException();
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException($routeInfo[1]);
            case \FastRoute\Dispatcher::FOUND:
                /* @var Route $route */
                [, $route, $routeArgs] = $routeInfo;

                $this->validateRoute($route);

                $request->attributes->set('route', $route);
                $request->attributes->set('args', $routeArgs);

                // transfer route attributes to the request attributes
                foreach ($route->getAttributes() as $key => $value) {
                    $request->attributes->set($key, $value);
                }

                $handler = $route->getHandler();
                $pipeline = $handler->getPipeline();

                // invoke middleware pipeline if exist
                if (null !== $pipeline) {
                    return $pipeline->handle($request);
                }

                // or invoke controller directly
                return $this->invoker->invoke($handler->getClosure(), $routeArgs, $request);
        }

        throw new RouteNotFoundException();
    }

    protected function validateRoute($route): void
    {
        if (!$route instanceof Route) {
            if (\is_object($route)) {
                $instance = \get_class($route);
            } else {
                $instance = \gettype($route);
            }

            throw new \RuntimeException(
                'Route must be instance of \MakiseCo\Http\Router\Route ' .
                'instance of ' . $instance . 'given'
            );
        }
    }
}
