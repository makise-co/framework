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
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Database\DatabaseManager;
use MakiseCo\Http\Events\WorkerExit;
use MakiseCo\Http\Events\WorkerStarted;
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
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpServiceProvider implements ServiceProviderInterface
{
    private EventDispatcher $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function register(Container $container): void
    {
        // initialize database if possible
        if ($container->has(DatabaseManager::class)) {
            $this->dispatcher->addListener(
                WorkerStarted::class,
                function () use ($container) {
                    $container->get(DatabaseManager::class)->initPools();
                }
            );

            $this->dispatcher->addListener(
                WorkerExit::class,
                function () use ($container) {
                    $container->get(DatabaseManager::class)->closePools();
                }
            );
        }

        $container->set(
            HttpServer::class,
            function (Container $container, ConfigRepositoryInterface $config) {
                return new HttpServer(
                    $container->make(HttpKernel::class),
                    $container->make(EventDispatcher::class),
                    $config->get('http.swoole', []),
                    $config->get('app.name')
                );
            }
        );

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

        $container->set(RouteCollector::class, function (Container $container, ConfigRepositoryInterface $config) {
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
            function (Container $container, ConfigRepositoryInterface $config) {
                return new RequestHandler(
                    $container->make(ExceptionHandlerMiddleware::class),
                    $container->get(RouteDispatchHandler::class),
                    $container->make(MiddlewarePipelineFactory::class),
                    $config->get('http.middleware', [])
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
            function (ConfigRepositoryInterface $config, MiddlewareFactory $factory) {
                $container = new MiddlewareContainer();

                // boot global middlewares
                foreach ($config->get('http.middleware', []) as $middleware) {
                    $container->add($factory->create($middleware));
                }

                return $container;
            }
        );
    }

    protected function loadRoutes(Container $container, ConfigRepositoryInterface $config, RouteCollector $routes): void
    {
        foreach ($config->get('http.routes', []) as $file) {
            include $file;
        }
    }
}
