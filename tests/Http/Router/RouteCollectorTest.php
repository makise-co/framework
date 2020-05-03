<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Http\Router;

use DI\Container;
use MakiseCo\Http\Handler\ControllerInvoker;
use MakiseCo\Http\Handler\RouteInvokeHandler;
use MakiseCo\Http\JsonResponse;
use MakiseCo\Http\Router\Exception\WrongRouteHandlerException;
use MakiseCo\Http\Router\MiddlewareContainer;
use MakiseCo\Http\Router\MiddlewareFactory;
use MakiseCo\Http\Router\MiddlewareHelper;
use MakiseCo\Http\Router\MiddlewarePipelineFactory;
use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\Router\RouteHandlerFactory;
use MakiseCo\Tests\Http\Router\Stubs\Middleware1;
use MakiseCo\Tests\Http\Router\Stubs\Middleware2;
use MakiseCo\Tests\Http\Router\Stubs\Middleware3;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RouteCollectorTest extends TestCase
{
    protected ?Container $container = null;

    protected function getContainer(): Container
    {
        if (null === $this->container) {
            return $this->container = (new \DI\ContainerBuilder())->build();
        }

        return $this->container;
    }

    protected function getCollector(): RouteCollector
    {
        $container = $this->getContainer();

        $invokeHandler = new RouteInvokeHandler(new ControllerInvoker($container));
        $handlerFactory = new RouteHandlerFactory($container);

        $middlewareContainer = new MiddlewareContainer();
        $middlewareFactory = new MiddlewareFactory($container);

        $middlewarePipelineFactory = new MiddlewarePipelineFactory($middlewareContainer);

        return new RouteCollector(
            $handlerFactory,
            new MiddlewareHelper(
                $invokeHandler,
                $middlewareContainer,
                $middlewareFactory,
                $middlewarePipelineFactory
            ),
            new \FastRoute\RouteParser\Std(),
            new \FastRoute\DataGenerator\GroupCountBased()
        );
    }

    public function testAddRouteWithAttributes(): void
    {
        $collector = $this->getCollector();

        $route = $collector
            ->get('test', function (): JsonResponse {
                return new JsonResponse([]);
            })
            ->withAttribute('some_attr', 'Makise');

        $this->assertEquals('Makise', $route->getAttribute('some_attr'));
    }

    public function testAddRouteWithMiddleware(): void
    {
        $collector = $this->getCollector();

        $route = $collector
            ->get('test', function (): JsonResponse {
                return new JsonResponse([]);
            })
            ->withMiddleware(Middleware1::class)
            ->withMiddleware(Middleware2::class);

        $this->assertEquals(
            [Middleware1::class, Middleware2::class],
            $route->getParameter('middleware')
        );

        $this->assertNotNull($route->getHandler()->getPipeline());
    }

    public function testAddRoute(): void
    {
        $collector = $this->getCollector();

        $collector->get('test', function (): JsonResponse {
            return new JsonResponse([]);
        });
        $collector->get('some', function (): ResponseInterface {
            return new JsonResponse([]);
        });
        $collector->get('another/', function (): ResponseInterface {
            return new JsonResponse([]);
        });
        $collector->get('another/one', function (): ResponseInterface {
            return new JsonResponse([]);
        });
        $collector->get('controller-index', __NAMESPACE__ . '\\Stubs\\StubController@index');

        $data = $collector->getData()[0]['GET'];
        $keys = \array_keys($data);

        $this->assertCount(5, $data);

        $this->assertEquals('/test', $keys[0]);
        $this->assertEquals('/some', $keys[1]);
        $this->assertEquals('/another', $keys[2]);
        $this->assertEquals('/another/one', $keys[3]);
        $this->assertEquals('/controller-index', $keys[4]);
    }

    /**
     * @dataProvider handlerWrongReturnTypeProvider
     * @param $badRoute
     */
    public function testAddRouteWrongReturnType($badRoute): void
    {
        $this->expectException(WrongRouteHandlerException::class);

        $collector = $this->getCollector();
        $collector->get('test', $badRoute);
    }

    public function handlerWrongReturnTypeProvider(): array
    {
        $badHandlers = [
            fn() => 1,
            function () {
                return new JsonResponse([]);
            },
            function (): \stdClass {
                return new \stdClass();
            },
            __NAMESPACE__ . '\\Stubs\\StubController@wrongRoute1',
            __NAMESPACE__ . '\\Stubs\\StubController@wrongRoute2',
        ];

        return \array_map(fn($handler) => [$handler], $badHandlers);
    }

    /**
     * @dataProvider handlerNotInvokableProvider
     * @param $badRoute
     */
    public function testAddRouteNotInvokableHandler($badRoute): void
    {
        $this->expectException(WrongRouteHandlerException::class);

        $collector = $this->getCollector();
        $collector->get('test', $badRoute);
    }

    public function handlerNotInvokableProvider(): array
    {
        $badHandlers = [
            'someClass::someMethod',
            'someFunction',
            'SomeController@index',
            [new \stdClass, 'index']
        ];

        return \array_map(fn($handler) => [$handler], $badHandlers);
    }

    public function testAddGroup(): void
    {
        $collector = $this->getCollector();

        $collector->addGroup(
            'admin',
            ['namespace' => __NAMESPACE__ . '\\Stubs\\'],
            function (RouteCollector $routes) {
                $routes->get('/', 'StubController@index');
            }
        );

        $collector->addGroup(
            '/',
            ['namespace' => __NAMESPACE__ . '\\Stubs\\'],
            function (RouteCollector $routes) {
                $routes->get('/', 'StubController@index');
            }
        );

        $data = $collector->getData()[0]['GET'];
        $keys = \array_keys($data);

        $this->assertCount(2, $data);
        $this->assertEquals('/admin', $keys[0]);
        $this->assertEquals('/', $keys[1]);
    }

    public function testAddNestedGroup(): void
    {
        $collector = $this->getCollector();

        $collector->addGroup(
            'admin',
            [
                'namespace' => __NAMESPACE__ . '\\Stubs\\',
                'middleware' => Middleware1::class,
                'attribute1' => ['some', 'some2'],
            ],
            function (RouteCollector $routes) {
                $route = $routes->get('/', 'StubController@index');

                $this->assertEquals(
                    ['some', 'some2'],
                    $route->getAttribute('attribute1')
                );

                $routes->addGroup(
                    'news',
                    [
                        'namespace' => __NAMESPACE__ . '\\Stubs\\SubStubs\\',
                        'middleware' => Middleware2::class,
                    ],
                    function (RouteCollector $routes) {
                        $route = $routes->get('/some', 'SubStubController@index');

                        $this->assertEquals(
                            ['some', 'some2'],
                            $route->getAttribute('attribute1')
                        );
                    }
                );

                $routes->addGroup(
                    'feed',
                    [
                        'namespace' => __NAMESPACE__ . '\\Stubs\\SubStubs\\',
                        'middleware' => Middleware3::class,
                        'attribute1' => ['access2'],
                    ],
                    function (RouteCollector $routes) {
                        $route = $routes->get('/some', 'SubStubController@index');

                        $this->assertEquals(
                            ['some', 'some2', 'access2'],
                            $route->getAttribute('attribute1')
                        );
                    }
                );
            }
        );

        $data = $collector->getData()[0]['GET'];
        $keys = \array_keys($data);

        $this->assertCount(3, $data);

        $this->assertEquals('/admin', $keys[0]);
        $this->assertEquals(
            [Middleware1::class],
            $data[$keys[0]]->getParameter('middleware')
        );

        $this->assertEquals('/admin/news/some', $keys[1]);
        $this->assertEquals(
            [Middleware1::class, Middleware2::class],
            $data[$keys[1]]->getParameter('middleware')
        );

        $this->assertEquals('/admin/feed/some', $keys[2]);
        $this->assertEquals(
            [Middleware1::class, Middleware3::class],
            $data[$keys[2]]->getParameter('middleware')
        );
    }
}
