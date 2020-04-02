<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Router\Exception;

class WrongRouteHandlerException extends \RuntimeException
{
    protected $handler;

    public function __construct(string $message, $handler)
    {
        $this->handler = $handler;

        parent::__construct($message, 0, null);
    }

    public function getHandler()
    {
        return $this->handler;
    }
}
