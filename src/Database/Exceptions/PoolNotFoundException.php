<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Exceptions;

class PoolNotFoundException extends \RuntimeException
{
    public function __construct(string $pool)
    {
        parent::__construct("Database pool {$pool} not found");
    }
}
