<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console\Commands;

use MakiseCo\Config\AppConfigInterface;
use MakiseCo\Http\Router\Route;
use MakiseCo\Http\Router\RouteCollector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RoutesDumpCommand extends Command
{
    protected AppConfigInterface $config;
    protected RouteCollector $routes;

    public function __construct(AppConfigInterface $config, RouteCollector $routes)
    {
        $this->config = $config;
        $this->routes = $routes;

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
        $table->setHeaders(['Method', 'Path', 'Parameters', 'Handler']);

        $cnt = 0;

        foreach (\array_filter($this->routes->getData()) as $collection) {
            foreach ($collection as $routes) {
                /* @var \MakiseCo\Http\Router\Route $route */
                foreach ($routes as $route) {
                    $isRouteMap = \is_array($route) && \array_key_exists('routeMap', $route);
                    if ($isRouteMap) {
                        foreach ($route['routeMap'] as $mappedRoute) {
                            $route = $mappedRoute[0];

                            $table->setRow(
                                $cnt,
                                [
                                    $route->getMethod(),
                                    $route->getPath(),
                                    ...$this->getRouteInfo($route)
                                ]
                            );

                            $cnt++;
                        }
                    } else {
                        $table->setRow(
                            $cnt,
                            [
                                $route->getMethod(),
                                $route->getPath(),
                                ...$this->getRouteInfo($route)
                            ]
                        );

                        $cnt++;
                    }
                }
            }
        }

        $table->render();

        return 0;
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
                $this->config->getDirectory(),
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
                $parameterValue = \implode(',', $parameter);
            } else {
                $parameterValue = $parameter;
            }

            $parametersText .= "{$key}={$parameterValue}";
        }

        return [
            $parametersText,
            $handler
        ];
    }
}
