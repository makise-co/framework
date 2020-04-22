<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection;

use Closure;
use Smf\ConnectionPool\ConnectionPool;

class LazyConnection implements ConnectionInterface
{
    protected ConnectionPool $pool;
    protected ?ConnectionInterface $connection = null;

    public function __construct(ConnectionPool $pool)
    {
        $this->pool = $pool;
    }

    public function __destruct()
    {
        $this->returnConnection(true);
    }

    public function getConnection(): ConnectionInterface
    {
        if (null !== $this->connection) {
            return $this->connection;
        }

        return $this->connection = $this->pool->borrow();
    }

    protected function returnConnection(bool $force = false): void
    {
        $connection = $this->connection;

        // connection is taken from the pool and not in the transaction
        if (null !== $connection && ($force || 0 === $connection->getTransactionsLevel())) {
            $this->connection = null;
            $this->pool->return($connection);
        }
    }

    public function select(string $query, array $bindings = []): array
    {
        $connection = $this->getConnection();

        try {
            return $connection->select($query, $bindings);
        } finally {
            $this->returnConnection();
        }
    }

    public function delete(string $query, array $bindings = []): int
    {
        $connection = $this->getConnection();

        try {
            return $connection->delete($query, $bindings);
        } finally {
            $this->returnConnection();
        }
    }

    public function update(string $query, array $bindings = []): int
    {
        $connection = $this->getConnection();

        try {
            return $connection->update($query, $bindings);
        } finally {
            $this->returnConnection();
        }
    }

    public function insert(string $query, array $bindings = []): bool
    {
        $connection = $this->getConnection();

        try {
            return $connection->insert($query, $bindings);
        } finally {
            $this->returnConnection();
        }
    }

    public function cursor(string $query, array $bindings = []): \Generator
    {
        $connection = $this->getConnection();

        try {
            yield from $connection->cursor($query, $bindings);
        } finally {
            $this->returnConnection();
        }
    }

    public function unprepared(string $query): bool
    {
        $connection = $this->getConnection();

        try {
            return $connection->unprepared($query);
        } finally {
            $this->returnConnection();
        }
    }

    public function begin(): void
    {
        $connection = $this->getConnection();

        try {
            $connection->begin();
        } finally {
            $this->returnConnection();
        }
    }

    public function commit(): void
    {
        $connection = $this->getConnection();

        try {
            $connection->commit();
        } finally {
            $this->returnConnection();
        }
    }

    public function rollback(): void
    {
        $connection = $this->getConnection();

        try {
            $connection->rollback();
        } finally {
            $this->returnConnection();
        }
    }

    public function transaction(Closure $executor)
    {
        $connection = $this->getConnection();

        try {
            return $connection->transaction($executor);
        } finally {
            $this->returnConnection();
        }
    }

    public function getTransactionsLevel(): int
    {
        if (null === $this->connection) {
            return 0;
        }

        return $this->connection->getTransactionsLevel();
    }

    public function disconnect(): void
    {
        if (null !== $this->connection) {
            $this->connection->disconnect();
        }
    }
}
