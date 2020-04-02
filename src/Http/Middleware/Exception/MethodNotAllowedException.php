<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Middleware\Exception;

use MakiseCo\Http\Exceptions\HttpException;

class MethodNotAllowedException extends HttpException
{
    private array $allowedMethods;

    public function __construct(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;

        parent::__construct(415, 'Method Not Allowed');
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
