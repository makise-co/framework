<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Handler;

use MakiseCo\Http\Middleware\ExceptionHandlerMiddleware;
use MakiseCo\Http\Router\MiddlewarePipeline;
use MakiseCo\Http\Router\MiddlewarePipelineFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    private MiddlewarePipeline $pipeline;

    public function __construct(
        ExceptionHandlerMiddleware $exceptionHandlerMiddleware,
        RouteDispatchHandler $dispatchHandler,
        MiddlewarePipelineFactory $pipelineFactory,
        array $middlewares
    ) {
        // make pipeline for global middlewares
        if ([] === $middlewares) {
            $this->pipeline = new MiddlewarePipeline($exceptionHandlerMiddleware, $dispatchHandler);
        } else {
            $middlewares[] = $exceptionHandlerMiddleware;
            $pipeline = $pipelineFactory->create($dispatchHandler, $middlewares);

            $this->pipeline = $pipeline;
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->pipeline->handle($request);
    }
}
