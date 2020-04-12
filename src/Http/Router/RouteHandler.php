<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router;

use Closure;

class RouteHandler
{
    protected Closure $closure;
    protected ?MiddlewarePipeline $pipeline = null;

    public function __construct(Closure $closure, ?MiddlewarePipeline $pipeline)
    {
        $this->closure = $closure;
        $this->pipeline = $pipeline;
    }

    /**
     * Get closure that will invoke route action
     *
     * @return Closure
     */
    public function getClosure(): Closure
    {
        return $this->closure;
    }

    /**
     * Get middleware pipeline that should be executed before route action
     *
     * @return MiddlewarePipeline|null
     */
    public function getPipeline(): ?MiddlewarePipeline
    {
        return $this->pipeline;
    }

    public function setPipeline(MiddlewarePipeline $pipeline): void
    {
        if (null !== $this->pipeline) {
            throw new \LogicException('Pipeline could be set only once');
        }

        $this->pipeline = $pipeline;
    }
}
