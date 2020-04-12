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
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @var Closure[]
     */
    protected array $handlers = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|array|callable|Closure $handler
     * @param string $namespace
     * @return Closure
     */
    public function create($handler, string $namespace = ''): Closure
    {
        if ($handler instanceof Closure) {
            $this->validateReturnType($handler, $handler);

            return $handler;
        }

        if (is_callable($handler)) {
            // TODO: Support caching for array syntax

            $closure = Closure::fromCallable($handler);
            $this->validateReturnType($handler, $closure);

            return $closure;
        }

        if (is_string($handler)) {
            $closure = $this->createFromString($namespace . $handler);
            $this->validateReturnType($handler, $closure);

            return $closure;
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

    protected function validateReturnType($handler, Closure $closure): void
    {
        $reflectionFunction = new \ReflectionFunction($closure);
        $returnType = $reflectionFunction->getReturnType();
        if (null === $returnType || $returnType->allowsNull()) {
            throw new Exception\WrongRouteHandlerException(
                'Handler must declare its return type to the ResponseInterface or its implementation (not null)',
                $handler
            );
        }

        $typeName = $returnType->getName();
        if ($typeName === ResponseInterface::class) {
            return;
        }

        try {
            $reflectionClass = new \ReflectionClass($typeName);
        } catch (\ReflectionException $e) {
            throw new Exception\WrongRouteHandlerException(
                'Handler must declare its return type to the ResponseInterface or its implementation (not null)',
                $handler
            );
        }

        if (!$reflectionClass->implementsInterface(ResponseInterface::class)) {
            throw new Exception\WrongRouteHandlerException(
                'Handler must declare its return type to the ResponseInterface or its implementation (not null)',
                $handler
            );
        }
    }
}
