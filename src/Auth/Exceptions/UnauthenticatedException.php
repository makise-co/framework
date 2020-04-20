<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Exceptions;

use MakiseCo\Http\Exceptions\HttpException;

class UnauthenticatedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(401, 'unauthenticated');
    }
}
