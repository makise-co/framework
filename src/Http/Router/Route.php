<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

class Route
{
    /**
     * @var string[] HTTP methods list
     */
    protected array $methods;

    /**
     * @var string HTTP url
     */
    protected string $path;

    /**
     * @var array the list of regexp
     */
    protected array $patterns;

    /**
     * @var RouteHandler
     */
    protected RouteHandler $handler;

    /**
     * @var array the parameters list inherited from route group
     */
    protected array $parameters;

    /**
     * @var array the attributes list that will be applied to request attributes
     */
    protected array $attributes = [];

    /**
     * @var MiddlewareHelper
     */
    protected MiddlewareHelper $middlewareHelper;

    /**
     * Route constructor.
     * @param array $methods
     * @param string $path
     * @param array $patterns
     * @param RouteHandler $handler
     * @param array $parameters
     * @param MiddlewareHelper $middlewareHelper
     */
    public function __construct(
        array $methods,
        string $path,
        array $patterns,
        RouteHandler $handler,
        array $parameters,
        MiddlewareHelper $middlewareHelper
    ) {
        $this->methods = $methods;
        $this->path = $path;
        $this->patterns = $patterns;
        $this->handler = $handler;
        $this->parameters = $parameters;
        $this->middlewareHelper = $middlewareHelper;
    }

    public function withAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function withMiddleware(string $middleware): self
    {
        if (!\array_key_exists('middleware', $this->parameters)) {
            $this->parameters['middleware'] = [];
        }

        $this->parameters['middleware'][] = $middleware;

        $pipeline = $this->middlewareHelper->buildPipeline($this->parameters['middleware']);
        $this->handler->setPipeline($pipeline);

        return $this;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function getHandler(): RouteHandler
    {
        return $this->handler;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }
}
