<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use DI\Container;
use MakiseCo\Config\AppConfigInterface;
use MakiseCo\Http\Exceptions\ExceptionHandler;
use MakiseCo\Http\Exceptions\ExceptionHandlerInterface;
use MakiseCo\Http\Handler\ControllerInvoker;
use MakiseCo\Http\Handler\RequestHandler;
use MakiseCo\Http\Handler\RouteDispatchHandler;
use MakiseCo\Http\Handler\RouteInvokeHandler;
use MakiseCo\Http\Middleware\ExceptionHandlerMiddleware;
use MakiseCo\Http\Router\MiddlewareContainer;
use MakiseCo\Http\Router\MiddlewareFactory;
use MakiseCo\Http\Router\MiddlewarePipelineFactory;
use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\Router\RouteHandlerFactory;
use MakiseCo\Providers\ServiceProviderInterface;

class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            ExceptionHandlerInterface::class,
            fn() => $container->make(ExceptionHandler::class)
        );

        $container->set(
            ControllerInvoker::class,
            fn(Container $container) => new ControllerInvoker($container)
        );

        $container->set(
            RouteInvokeHandler::class,
            fn(Container $container) => new RouteInvokeHandler($container->get(ControllerInvoker::class))
        );

        $container->set(RouteCollector::class, function (Container $container, AppConfigInterface $config) {
            $middlewareContainer = $container->get(MiddlewareContainer::class);

            $routes = new RouteCollector(
                $container->get(RouteInvokeHandler::class),
                new RouteHandlerFactory($container),
                $middlewareContainer,
                new MiddlewareFactory($container),
                new MiddlewarePipelineFactory($middlewareContainer),
                new \FastRoute\RouteParser\Std(),
                new \FastRoute\DataGenerator\GroupCountBased()
            );

            $this->loadRoutes($container, $config, $routes);

            return $routes;
        });

        $container->set(
            \FastRoute\Dispatcher::class,
            function (RouteCollector $routes) {
                return new \FastRoute\Dispatcher\GroupCountBased($routes->getData());
            }
        );

        $container->set(
            RequestHandler::class,
            function (Container $container, AppConfigInterface $config) {
                return new RequestHandler(
                    $container->make(ExceptionHandlerMiddleware::class),
                    $container->get(RouteDispatchHandler::class),
                    $container->make(MiddlewarePipelineFactory::class),
                    $config->getGlobalMiddlewares()
                );
            }
        );

        $container->set(
            RouteDispatchHandler::class,
            function (Container $container) {
                return new RouteDispatchHandler(
                    $container->get(\FastRoute\Dispatcher::class),
                    $container->get(ControllerInvoker::class),
                );
            }
        );

        $container->set(
            MiddlewareContainer::class,
            function (AppConfigInterface $config, MiddlewareFactory $factory) {
                $container = new MiddlewareContainer();

                // boot global middlewares
                foreach ($config->getGlobalMiddlewares() as $middleware) {
                    $container->add($factory->create($middleware));
                }

                return $container;
            }
        );
    }

    protected function loadRoutes(Container $container, AppConfigInterface $config, RouteCollector $routes): void
    {
        foreach ($config->getHttpRoutes() as $file) {
            include_once $file;
        }
    }
}
