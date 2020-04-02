<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use DI\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

use function get_class;
use function array_key_exists;

class MiddlewareContainer implements ContainerInterface
{
    protected array $items = [];

    public function add(MiddlewareInterface $middleware): void
    {
        $name = get_class($middleware);

        $this->items[$name] = $middleware;
    }

    /**
     * @param string $id
     * @return MiddlewareInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function get($id): MiddlewareInterface
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Middleware {$id} not found");
        }

        return $this->items[$id];
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->items);
    }
}
