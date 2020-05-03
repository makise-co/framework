<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use MakiseCo\Http\Handler\RouteInvokeHandler;

class MiddlewareHelper
{
    protected RouteInvokeHandler $routeInvokeHandler;
    protected MiddlewareContainer $middlewareContainer;
    protected MiddlewareFactory $middlewareFactory;
    protected MiddlewarePipelineFactory $middlewarePipelineFactory;

    public function __construct(
        RouteInvokeHandler $routeInvokeHandler,
        MiddlewareContainer $middlewareContainer,
        MiddlewareFactory $middlewareFactory,
        MiddlewarePipelineFactory $middlewarePipelineFactory
    ) {
        $this->routeInvokeHandler = $routeInvokeHandler;
        $this->middlewareContainer = $middlewareContainer;
        $this->middlewareFactory = $middlewareFactory;
        $this->middlewarePipelineFactory = $middlewarePipelineFactory;
    }

    public function registerMiddleware(array $middlewareList): void
    {
        foreach ($middlewareList as $middleware) {
            if (!$this->middlewareContainer->has($middleware)) {
                $middlewareObj = $this->middlewareFactory->create($middleware);
                $this->middlewareContainer->add($middlewareObj);
            }
        }
    }

    public function buildPipeline(array $middlewareList): MiddlewarePipeline
    {
        $this->registerMiddleware($middlewareList);

        return $this->middlewarePipelineFactory->create(
            $this->routeInvokeHandler,
            $middlewareList
        );
    }
}
