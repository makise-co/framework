<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_reverse;

class MiddlewarePipelineFactory
{
    protected MiddlewareContainer $middlewareContainer;

    public function __construct(MiddlewareContainer $middlewareContainer)
    {
        $this->middlewareContainer = $middlewareContainer;
    }

    /**
     * @param RequestHandlerInterface $requestHandler
     * @param string[]|MiddlewareInterface[] $middlewares
     * @return MiddlewarePipeline
     */
    public function create(RequestHandlerInterface $requestHandler, array $middlewares): MiddlewarePipeline
    {
        if ([] === $middlewares) {
            throw new \InvalidArgumentException('Middleware list cannot be empty');
        }

        $middlewares = array_reverse($middlewares);

        $pipe = $requestHandler;

        foreach ($middlewares as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $pipe = new MiddlewarePipeline($middleware, $pipe);

                continue;
            }

            $pipe = new MiddlewarePipeline(
                $this->middlewareContainer->get($middleware),
                $pipe
            );
        }

        return $pipe;
    }
}
