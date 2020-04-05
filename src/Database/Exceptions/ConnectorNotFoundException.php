<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Exceptions;

class ConnectorNotFoundException extends \RuntimeException
{
    public function __construct(string $driver)
    {
        parent::__construct("Database connector not found for driver {$driver}");
    }
}
