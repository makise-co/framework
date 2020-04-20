<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Http\Middleware;

use MakiseCo\Auth\GuardInterface;
use MakiseCo\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    protected GuardInterface $guard;

    public function __construct(GuardInterface $guard)
    {
        $this->guard = $guard;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request instanceof Request) {
            $this->guard->authenticate($request);
        }

        return $handler->handle($request);
    }
}
