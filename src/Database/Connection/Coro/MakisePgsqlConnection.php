<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection\Coro;

use Closure;
use Generator;
use MakiseCo\Database\Connection\ConnectionInterface;
use MakiseCo\Database\Exceptions\QueryException;
use MakiseCo\Postgres\CommandResult;
use MakiseCo\Postgres\Connection;
use MakiseCo\Postgres\Exception\ConnectionException;
use MakiseCo\Postgres\Exception\QueryExecutionError;
use MakiseCo\Postgres\ResultSet;
use MakiseCo\Postgres\Transaction;
use pq\Result;

class MakisePgsqlConnection implements ConnectionInterface
{
    protected Connection $connection;
    protected int $transactionLevel = 0;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function select(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings, function (string $query, array $bindings) {
            $statement = $this->connection->prepare($query);
            $resultSet = $statement->execute($bindings);

            if ($resultSet instanceof ResultSet) {
                return $resultSet->fetchAll(Result::FETCH_OBJECT);
            }

            return [];
        });
    }

    public function delete(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function update(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function insert(string $query, array $bindings = []): bool
    {
        return 0 !== $this->affectingStatement($query, $bindings);
    }

    public function cursor(string $query, array $bindings = []): Generator
    {
        $resultSet = $this->run($query, $bindings, function (string $query, array $bindings) {
            // First we will create a statement for the query. Then, we will set the fetch
            // mode and prepare the bindings for the query. Once that's done we will be
            // ready to execute the query against the database and return the cursor.
            $statement = $this->connection->prepare($query);

            // Next, we'll execute the query against the database and return the statement
            // so we can return the cursor. The cursor will use a PHP generator to give
            // back one row at a time without using a bunch of memory to render them.
            return $statement->execute($bindings);
        });

        if ($resultSet instanceof ResultSet) {
            while ($record = $resultSet->fetch(\pq\Result::FETCH_OBJECT)) {
                yield $record;
            }
        }
    }

    public function unprepared(string $query): bool
    {
        return $this->run($query, [], function ($query): bool {
            $this->connection->execute($query);

            return true;
        });
    }

    public function begin(): Transaction
    {
        return $this->run('BEGIN', [], function (): Transaction {
            $this->transactionLevel++;

            return $this->connection->beginTransaction();
        });
    }

    public function commit(): void
    {
        if (0 === $this->transactionLevel) {
            return;
        }

        $this->run('COMMIT', [], function () {
            $this->transactionLevel--;

            return $this->connection->query('COMMIT');
        });
    }

    public function rollback(): void
    {
        if (0 === $this->transactionLevel) {
            return;
        }

        $this->run('ROLLBACK', [], function () {
            $this->transactionLevel--;

            return $this->connection->query('ROLLBACK');
        });
    }

    public function transaction(Closure $executor)
    {
        $transaction = $this->begin();

        try {
            $retVal = $executor($transaction);
        } catch (\Throwable $e) {
            if ($e instanceof ConnectionException) {
                $this->transactionLevel = 0;

                throw $e;
            }

            $transaction->rollback();

            throw $e;
        }

        $this->run('COMMIT', [], function () use ($transaction) {
            $transaction->commit();
            $this->transactionLevel--;
        });

        return $retVal;
    }

    public function getTransactionsLevel(): int
    {
        return $this->transactionLevel;
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function affectingStatement(string $query, array $bindings = []): int
    {
        return $this->run($query, $bindings, function (string $query, array $bindings): int {
            $statement = $this->connection->prepare($query);
            $commandRes = $statement->execute($bindings);

            if ($commandRes instanceof CommandResult) {
                return $commandRes->getAffectedRowCount();
            }

            return 0;
        });
    }

    public function disconnect(): void
    {
        $this->connection->disconnect();
        $this->transactionLevel = 0;
    }

    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    /**
     * Run a SQL statement
     *
     * @param string $query
     * @param array $bindings
     * @param Closure $run
     * @return mixed
     */
    protected function run(string $query, array $bindings, Closure $run)
    {
        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        try {
            return $run($query, $bindings);
        } catch (ConnectionException $e) {
            $this->connection->disconnect();
            $this->connection->connect();

            return $run($query, $bindings);
        } catch (QueryExecutionError $e) {
            $diag = $e->getDiagnostics();

            throw new QueryException(
                $e->getMessage(),
                (int)$e->getCode(),
                $diag['sqlstate'] ?? '',
                $query,
                $bindings,
                $e
            );
        }
    }
}
