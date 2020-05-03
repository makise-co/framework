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
use MakiseCo\Util\Arr;

use function array_key_exists;
use function mb_strpos;
use function rtrim;

class RouteCollector
{
    protected RouteHandlerFactory $handlerFactory;

    protected MiddlewareHelper $middlewareHelper;

    protected RouteParser $routeParser;

    protected DataGenerator $dataGenerator;

    protected string $currentGroupPrefix;

    /**
     * @var array<string,mixed>
     */
    protected array $currentGroupParameters = [];

    /**
     * @var Route[] holds all routes
     */
    protected array $routes = [];

    /**
     * Constructs a route collector.
     *
     * @param RouteHandlerFactory $handlerFactory
     * @param MiddlewareHelper $middlewareHelper
     * @param RouteParser $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(
        RouteHandlerFactory $handlerFactory,
        MiddlewareHelper $middlewareHelper,
        RouteParser $routeParser,
        DataGenerator $dataGenerator
    ) {
        $this->handlerFactory = $handlerFactory;
        $this->middlewareHelper = $middlewareHelper;

        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
        $this->currentGroupPrefix = '';
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed $handler
     *
     * @return Route
     */
    public function addRoute($httpMethod, string $route, $handler): Route
    {
        $route = $this->fixGroupPrefix($route);

        $route = $this->currentGroupPrefix . $route;
        if ('' === $route) {
            $route = '/';
        }

        // Parse route URL
        $routeDatas = $this->routeParser->parse($route);

        // Create route handler
        $handler = $this->handlerFactory->create($handler, $this->currentGroupParameters['namespace'] ?? '');
        $pipeline = null;

        $routeInstance = new Route(
            (array)$httpMethod,
            $route,
            $routeDatas,
            new RouteHandler($handler, $pipeline),
            $this->currentGroupParameters + ['group' => $this->currentGroupPrefix],
            $this->middlewareHelper
        );
        $this->addGroupParametersToRoute($routeInstance);

        foreach ((array)$httpMethod as $method) {
            foreach ($routeDatas as $routeData) {
                $this->dataGenerator->addRoute(
                    $method,
                    $routeData,
                    $routeInstance
                );
            }
        }

        $this->routes[] = $route;

        return $routeInstance;
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

        $prefix = $this->fixGroupPrefix($prefix);

        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $this->currentGroupParameters = Arr::mergeRecursive(false, $previousGroupParameters, $parameters);

        $callback($this);

        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupParameters = $previousGroupParameters;
    }

    /**
     * Adds a GET route to the collection
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     * @return Route
     */
    public function get(string $route, $handler): Route
    {
        return $this->addRoute('GET', $route, $handler);
    }

    /**
     * Adds a POST route to the collection
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     * @return Route
     */
    public function post(string $route, $handler): Route
    {
        return $this->addRoute('POST', $route, $handler);
    }

    /**
     * Adds a PUT route to the collection
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     * @return Route
     */
    public function put(string $route, $handler): Route
    {
        return $this->addRoute('PUT', $route, $handler);
    }

    /**
     * Adds a DELETE route to the collection
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     * @return Route
     */
    public function delete(string $route, $handler): Route
    {
        return $this->addRoute('DELETE', $route, $handler);
    }

    /**
     * Adds a PATCH route to the collection
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     * @return Route
     */
    public function patch(string $route, $handler): Route
    {
        return $this->addRoute('PATCH', $route, $handler);
    }

    /**
     * Adds a HEAD route to the collection
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     *
     * @param string $route
     * @param mixed $handler
     * @return Route
     */
    public function head(string $route, $handler): Route
    {
        return $this->addRoute('HEAD', $route, $handler);
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

    /**
     * Returns all routes
     *
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    protected function addGroupParametersToRoute(Route $route): void
    {
        foreach ($this->currentGroupParameters as $key => $value) {
            switch ($key) {
                case 'middleware':
                    $pipeline = $this->middlewareHelper->buildPipeline($this->currentGroupParameters['middleware']);

                    $route->getHandler()->setPipeline($pipeline);
                    break;
                // ignore namespace parameter
                case 'namespace':
                    break;
                // copy all other group parameters to route attributes
                default:
                    $route->withAttribute($key, $value);
            }
        }
    }

    protected function fixGroupPrefix(string $prefix): string
    {
        // add slash to the begin of prefix
        if (0 !== mb_strpos($prefix, '/')) {
            $prefix = '/' . $prefix;
        }

        // remove slash from the end of prefix
        return rtrim($prefix, '/');
    }

    /**
     * @param array<string> $middlewareList
     */
    protected function handleMiddlewareParameter(array $middlewareList): void
    {
        $this->middlewareHelper->registerMiddleware($middlewareList);
    }
}
