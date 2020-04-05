<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Exceptions;

use Throwable;

class QueryException extends \RuntimeException
{
    protected string $query;
    protected array $bindings = [];

    public function __construct(string $message, int $code, string $query, array $bindings, ?Throwable $previous)
    {
        parent::__construct($message, $code, $previous);

        $this->query = $query;
        $this->bindings = $bindings;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
