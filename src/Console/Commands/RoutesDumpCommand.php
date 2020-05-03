<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\ApplicationInterface;
use MakiseCo\Http\Router\Route;
use MakiseCo\Http\Router\RouteCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoutesDumpCommand extends Command
{
    protected ApplicationInterface $app;
    protected RouteCollector $routeCollector;

    public function __construct(ApplicationInterface $config, RouteCollector $routes)
    {
        $this->app = $config;
        $this->routeCollector = $routes;

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->setName('routes:dump');

        $this->setDescription('Prints all app HTTP routes');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders(['Method', 'Path', 'Handler', 'Parameters', 'Attributes']);
        $table->setColumnMaxWidth(0, 30);
        $table->setColumnMaxWidth(1, 60);
        $table->setColumnMaxWidth(2, 60);
        $table->setColumnMaxWidth(3, 30);
        $table->setColumnMaxWidth(4, 30);

        $cnt = 0;

        foreach ($this->routeCollector->getRoutes() as $route) {
            $table->setRow(
                $cnt,
                $this->processRoute($route)
            );

            $cnt++;
        }

        $table->render();

        return 0;
    }

    protected function processRoute(Route $route): array
    {
        return [
            \implode(', ', $route->getMethods()),
            $route->getPath(),
            ...$this->getRouteInfo($route),
            $this->getRouteAttributesString($route),
        ];
    }

    protected function getRouteAttributesString(Route $route): string
    {
        $attributes = $route->getAttributes();
        $attributesStr = '';

        \array_walk($attributes, static function ($value, string $key) use (&$attributesStr) {
            if (\is_array($value)) {
                $value = \implode(', ', $value);
            } elseif (\is_object($value)) {
                $value = \get_class($value);
            }

            $attributesStr .= "{$key}={$value}" . PHP_EOL;
        });

        return $attributesStr;
    }

    protected function getRouteInfo(Route $route): array
    {
        $routeHandler = $route->getHandler()->getClosure();

        $reflection = new \ReflectionFunction($routeHandler);

        $class = $reflection->getClosureScopeClass();
        $name = $reflection->getName();

        if (null === $class) {
            $handler = $reflection->getName();
        } elseif ($name === '{closure}') {
            $name = \str_replace(
                $this->app->getAppDir(),
                '',
                $reflection->getFileName()
            );

            $name .= '::' . $reflection->getStartLine();

            $handler = $name;
        } else {
            $handler = "{$class->getName()}::{$name}";
        }

        $parameters = $route->getParameters();
        $parametersText = '';

        unset($parameters['namespace']);

        foreach ($parameters as $key => $parameter) {
            if (\is_array($parameter)) {
                $parameterValue = \implode(', ', $parameter);
            } else {
                $parameterValue = $parameter;
            }

            if (empty($parameterValue)) {
                continue;
            }

            $parametersText .= "{$key}={$parameterValue}" . PHP_EOL;
        }

        return [
            $handler,
            $parametersText,
        ];
    }
}
