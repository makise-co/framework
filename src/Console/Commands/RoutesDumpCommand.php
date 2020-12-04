<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use Closure;
use MakiseCo\Http\Router\HandlerResolver\RouteHandlerPromise;
use MakiseCo\Http\Router\RouteCollectorInterface;
use MakiseCo\Http\Router\RouteInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Symfony\Component\Console\Helper\Table;

use function class_exists;
use function implode;
use function is_array;
use function is_callable;
use function is_string;
use function str_replace;

class RoutesDumpCommand extends AbstractCommand
{
    protected string $name = 'routes:dump';
    protected string $description = 'Prints all app HTTP routes';

    public function handle(RouteCollectorInterface $routeCollector): void
    {
        $table = new Table($this->output);
        $table->setHeaders(['Method', 'Path', 'Name', 'Handler']);
        $table->setColumnMaxWidth(0, 30);
        $table->setColumnMaxWidth(1, 60);
        $table->setColumnMaxWidth(2, 60);
        $table->setColumnMaxWidth(3, 60);

        $cnt = 0;

        foreach ($routeCollector->getRoutes() as $route) {
            $table->setRow(
                $cnt,
                $this->getRouteInfo($route)
            );

            $cnt++;
        }

        $table->render();
    }

    /**
     * @inheritDoc
     */
    public function getServices(): array
    {
        return [null];
    }

    protected function getRouteInfo(RouteInterface $route): array
    {
        return [
            implode(', ', $route->getMethods()),
            $route->getPath(),
            $route->getPath() !== ($name = $route->getName()) ? $name : '',
            $this->getRouteHandlerInfo($route->getHandler()),
        ];
    }

    protected function getRouteHandlerInfo(Closure $closure): string
    {
        $reflection = new ReflectionFunction($closure);
        if (($promise = $reflection->getClosureThis()) instanceof RouteHandlerPromise) {
            /** @var RouteHandlerPromise $promise */

            $handler = $promise->getRouteHandler();
            if ($handler instanceof Closure) {
                return $this->getClosureInfo($handler);
            }

            return $this->getPromisedHandlerInfo($handler);
        }

        return $this->getClosureInfo($closure);
    }

    protected function getPromisedHandlerInfo($handler): string
    {
        if (is_string($handler)) {
            if (0 !== strpos($handler, '@')) {
                $handler = explode('@', $handler, 2);
            } elseif (strpos($handler, '::') !== false) {
                $handler = explode('::', $handler, 2);
            }
        }

        if (is_callable($handler)) {
            // TODO with PHP 8 that should not be necessary to check this anymore
            if (!$this->isStaticCallToNonStaticMethod($handler)) {
                return $this->getClosureInfo(Closure::fromCallable($handler));
            }
        }

        // The callable is an array whose first item is a container entry name
        // e.g. ['some-container-entry', 'methodToCall']
        if (is_array($handler) && is_string($handler[0])) {
            if (!class_exists($handler[0])) {
                return "Class \"{$handler[0]}\" not found";
            }

            $refClass = new ReflectionClass($handler[0]);
            try {
                $refClass->getMethod($handler[1]);
            } catch (ReflectionException $e) {
                return "Method \"{$handler[1]}\" of class \"{$handler[0]}\" not found";
            }

            return $refClass->getName() . '::' . $handler[1];
        }

        return 'Bad route handler';
    }

    protected function getClosureInfo(Closure $closure): string
    {
        $reflection = new ReflectionFunction($closure);

        $class = $reflection->getClosureScopeClass();
        $name = $reflection->getName();

        if (null === $class) {
            $handler = $reflection->getName();
        } elseif ($name === '{closure}') {
            // remove app path
            $name = str_replace(
                $this->app->getAppDir(),
                '',
                $reflection->getFileName()
            );

            $name .= ':' . $reflection->getStartLine();

            $handler = $name;
        } else {
            $handler = "{$class->getName()}::{$name}";
        }

        return $handler;
    }

    /**
     * Check if the callable represents a static call to a non-static method.
     *
     * @param mixed $callable
     *
     * @throws ReflectionException
     */
    private function isStaticCallToNonStaticMethod($callable): bool
    {
        if (is_array($callable) && is_string($callable[0])) {
            [$class, $method] = $callable;
            $reflection = new ReflectionMethod($class, $method);

            return !$reflection->isStatic();
        }

        return false;
    }
}
