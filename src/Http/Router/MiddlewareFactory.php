<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use DI\Container;
use Psr\Http\Server\MiddlewareInterface;

use function class_exists;

class MiddlewareFactory
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $middleware): MiddlewareInterface
    {
        if (!class_exists($middleware)) {
            throw new Exception\WrongMiddlewareException(
                "Middleware class {$middleware} not found",
                $middleware
            );
        }

        $middlewareObj = $this->container->make($middleware);

        if (!$middlewareObj instanceof MiddlewareInterface) {
            throw new Exception\WrongMiddlewareException(
                "Middleware {$middleware} must implement MiddlewareInterface",
                $middleware
            );
        }

        return $middlewareObj;
    }
}
