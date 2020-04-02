<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use Closure;
use DI\Container;

use function array_key_exists;
use function class_exists;
use function count;
use function explode;
use function is_callable;
use function is_string;
use function strpos;

class RouteHandlerFactory
{
    protected Container $container;
    protected array $controllers = [];
    protected Closure $resolver;

    /**
     * @var Closure[]
     */
    protected array $handlers = [];

    public function __construct(Container $container, ?Closure $resolver = null)
    {
        $this->container = $container;
        if (null === $resolver) {
            $resolver = Closure::fromCallable(function (Route $route, array $args) use ($container) {
//                $container->call()
            });
        }
    }

    public function create($handler, string $namespace = ''): Closure
    {
        if ($handler instanceof Closure) {
            return $handler;
        }

        if (is_callable($handler)) {
            // TODO: Support caching for array syntax

            return Closure::fromCallable($handler);
        }

        if (is_string($handler)) {
            return $this->createFromString($namespace . $handler);
        }

        throw new Exception\WrongRouteHandlerException('Unsupported handler', $handler);
    }

    public function createFromString(string $handler): Closure
    {
        if (false === strpos($handler, '@')) {
            throw new Exception\WrongRouteHandlerException('Handler must be in format class@method', $handler);
        }

        $parts = explode('@', $handler);
        if (2 !== count($parts)) {
            throw new Exception\WrongRouteHandlerException('Handler must be in format class@method', $handler);
        }

        [$class, $method] = $parts;

        if (!class_exists($class)) {
            throw new Exception\WrongRouteHandlerException("Handler class {$class} not found", $handler);
        }

        if (array_key_exists($handler, $this->handlers)) {
            return $this->handlers[$handler];
        }

        if (array_key_exists($class, $this->controllers)) {
            $controller = $this->controllers[$class];
        } else {
            $controller = $this->container->make($class);
        }

        $callable = [$controller, $method];
        if (!is_callable($callable)) {
            throw new Exception\WrongRouteHandlerException('Handler is not callable', $handler);
        }

        $this->controllers[$class] = $controller;

        return $this->handlers[$handler] = Closure::fromCallable($callable);
    }
}
