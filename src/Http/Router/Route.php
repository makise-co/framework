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
    protected string $method;
    protected string $path;
    protected array $patterns;

    protected RouteHandler $handler;
    protected array $parameters;

    public function __construct(string $method, string $path, array $patterns, RouteHandler $handler, array $parameters)
    {
        $this->method = $method;
        $this->path = $path;
        $this->patterns = $patterns;
        $this->handler = $handler;
        $this->parameters = $parameters;
    }

    public function getMethod(): string
    {
        return $this->method;
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
