<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface|MiddlewareInterface
     */
    protected $handler;

    protected RequestHandlerInterface $next;

    public function __construct($handler, RequestHandlerInterface $next)
    {
        if (!$handler instanceof RequestHandlerInterface && !$handler instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException('handler must be instance of request' .
                'handler interface or middleware interface');
        }

        $this->handler = $handler;
        $this->next = $next;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->handler instanceof RequestHandlerInterface) {
            return $this->handler->handle($request);
        }

        return $this->handler->process($request, $this->next);
    }
}
