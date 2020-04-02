<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Exception;

class WrongMiddlewareException extends \RuntimeException
{
    protected $middleware;

    public function __construct(string $message, $middleware)
    {
        $this->middleware = $middleware;

        parent::__construct($message, 0, null);
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }
}
