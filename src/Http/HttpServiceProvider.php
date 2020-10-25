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
use MakiseCo\Http\Exceptions\JsonExceptionHandler;
use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\Router\RouteCollectorInterface;
use MakiseCo\Http\Router\RouteCollectorLazyFactory;
use MakiseCo\Http\Swoole\SwooleEmitter;
use MakiseCo\Http\Swoole\SwoolePsrRequestFactory;
use MakiseCo\Http\Swoole\SwoolePsrRequestFactoryInterface;
use MakiseCo\Middleware\ErrorHandlerInterface;
use MakiseCo\Middleware\ErrorHandlingMiddleware;
use MakiseCo\Middleware\MiddlewarePipeFactory;
use MakiseCo\Middleware\MiddlewareResolver;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpServiceProvider implements ServiceProviderInterface
{
    public const REQUEST_HANDLER = 'http.request_handler';

    public function register(Container $container): void
    {
        // register JsonExceptionHandler as default exception handler
        $container->set(ErrorHandlerInterface::class, \DI\get(JsonExceptionHandler::class));

        // register SwoolePsrRequest factory (converts Swoole Http Requests to PSR HTTP Requests)
        $container->set(SwoolePsrRequestFactoryInterface::class, \DI\get(SwoolePsrRequestFactory::class));

        // register route collector
        $container->set(
            RouteCollector::class,
            function (Container $container, ConfigRepositoryInterface $config) {
                $factory = new RouteCollectorLazyFactory(
                    [
                        \Laminas\Diactoros\ServerRequest::class,
                    ]
                );

                $collector = $factory->create($container);

                $this->loadRoutes($config, $collector);

                return $collector;
            }
        );
        $container->set(RouteCollectorInterface::class, \DI\get(RouteCollector::class));

        // register request handler
        $container->set(
            self::REQUEST_HANDLER,
            static function (
                Container $container,
                ConfigRepositoryInterface $config,
                RouteCollectorInterface $collector
            ) {
                $middlewares = [ErrorHandlingMiddleware::class];

                foreach ((array)$config->get('http.middleware', []) as $middleware) {
                    $middlewares[] = $middleware;
                }

                $middlewares[] = $collector->getRouter();

                return (new MiddlewarePipeFactory(new MiddlewareResolver($container)))
                    ->create($middlewares);
            }
        );

        // register HTTP server
        $container->set(
            HttpServer::class,
            static function (Container $container, ConfigRepositoryInterface $config) {
                return new HttpServer(
                    $container->get(EventDispatcher::class),
                    $container->make(SwoolePsrRequestFactoryInterface::class),
                    $container->make(SwooleEmitter::class),
                    $container->get(self::REQUEST_HANDLER),
                    $config->get('http.swoole', []),
                    $config->get('app.name')
                );
            }
        );
    }

    protected function loadRoutes(ConfigRepositoryInterface $config, RouteCollector $routes): void
    {
        foreach ($config->get('http.routes', []) as $file) {
            include $file;
        }
    }
}
