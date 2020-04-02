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
use function is_string;

class MiddlewareFactory
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create($middleware): MiddlewareInterface
    {
        if (is_string($middleware)) {
            if (!class_exists($middleware)) {
                throw new Exception\WrongMiddlewareException(
                    "Middleware class {$middleware} not found",
                    $middleware
                );
            }

            $middlewareObj = $this->container->make($middleware);
        } else {
            $middlewareObj = $middleware;
        }

        if (!$middlewareObj instanceof MiddlewareInterface) {
            throw new Exception\WrongMiddlewareException('Middleware must implement MiddlewareInterface', $middleware);
        }

        return $middlewareObj;
    }
}
