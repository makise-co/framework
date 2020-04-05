<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use MakiseCo\Http\Handler\RouteInvokeHandler;

use function array_key_exists;
use function array_merge_recursive;

class RouteCollector
{
    protected RouteInvokeHandler $routeInvokeHandler;

    protected RouteHandlerFactory $handlerFactory;

    protected MiddlewareContainer $middlewareContainer;

    protected MiddlewareFactory $middlewareFactory;

    protected MiddlewarePipelineFactory $middlewarePipelineFactory;

    protected RouteParser $routeParser;

    protected DataGenerator $dataGenerator;

    protected string $currentGroupPrefix;

    /**
     * @var array<string,mixed>
     */
    protected array $currentGroupParameters = [];

    /**
     * Constructs a route collector.
     *
     * @param RouteInvokeHandler $routeInvokeHandler
     * @param RouteHandlerFactory $handlerFactory
     * @param MiddlewareContainer $middlewareContainer
     * @param MiddlewareFactory $middlewareFactory
     * @param MiddlewarePipelineFactory $middlewarePipelineFactory
     * @param RouteParser $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(
        RouteInvokeHandler $routeInvokeHandler,
        RouteHandlerFactory $handlerFactory,
        MiddlewareContainer $middlewareContainer,
        MiddlewareFactory $middlewareFactory,
        MiddlewarePipelineFactory $middlewarePipelineFactory,
        RouteParser $routeParser,
        DataGenerator $dataGenerator
    ) {
        $this->routeInvokeHandler = $routeInvokeHandler;
        $this->handlerFactory = $handlerFactory;
        $this->middlewareContainer = $middlewareContainer;
        $this->middlewareFactory = $middlewareFactory;
        $this->middlewarePipelineFactory = $middlewarePipelineFactory;

        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->currentGroupPrefix = '';
        $this->middlewarePipelineFactory = $middlewarePipelineFactory;
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed $handler
     */
    public function addRoute($httpMethod, $route, $handler): void
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);

        $handler = $this->handlerFactory->create($handler, $this->currentGroupParameters['namespace'] ?? '');
        $pipeline = null;

        foreach ((array)$httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $routeInstance = new Route(
                    $method,
                    $route,
                    $routeData,
                    new RouteHandler($handler, $pipeline),
                    $this->currentGroupParameters
                );

                $this->dataGenerator->addRoute(
                    $method,
                    $routeData,
                    $routeInstance
                );

                if (array_key_exists('middleware', $this->currentGroupParameters)) {
                    $pipeline = $this->middlewarePipelineFactory->create(
                        $this->routeInvokeHandler,
                        $this->currentGroupParameters['middleware']
                    );

                    $routeInstance->getHandler()->setPipeline($pipeline);
                }
            }
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     * @param array<string,mixed> $parameters
     * @param callable $callback
     */
    public function addGroup(string $prefix, array $parameters, callable $callback): void
    {
        if (array_key_exists('middleware', $parameters)) {
            $parameters['middleware'] = (array)$parameters['middleware'];

            $this->handleMiddlewareParameter($parameters['middleware']);
        }

        $previousGroupPrefix = $this->currentGroupPrefix;
        $previousGroupParameters = $this->currentGroupParameters;

        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $this->currentGroupParameters = array_merge_recursive($previousGroupParameters, $parameters);

        $callback($this);

        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupParameters = $previousGroupParameters;
    }

    /**
     * @param array<string> $middlewareList
     */
    protected function handleMiddlewareParameter(array $middlewareList): void
    {
        foreach ($middlewareList as $middleware) {
            if (!$this->middlewareContainer->has($middleware)) {
                $middlewareObj = $this->middlewareFactory->create($middleware);
                $this->middlewareContainer->add($middlewareObj);
            }
        }
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function get($route, $handler): void
    {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function post($route, $handler): void
    {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function put($route, $handler): void
    {
        $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function delete($route, $handler): void
    {
        $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function patch($route, $handler): void
    {
        $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     */
    public function head($route, $handler): void
    {
        $this->addRoute('HEAD', $route, $handler);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array<array<mixed>>
     */
    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }
}
