<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection\Pdo;

use MakiseCo\Database\Connection\Connection;
use MakiseCo\Database\Exceptions\QueryException;
use PDO;
use PDOException;
use Closure;

use function is_string;
use function is_int;

class PdoConnection extends Connection
{
    use TransactionableTrait;

    protected ?PDO $pdo;
    protected Closure $reconnector;

    public function __construct(PDO $pdo, Closure $reconnector)
    {
        $this->pdo = $pdo;
        $this->reconnector = $reconnector;
    }

    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    public function setPdo(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    public function select(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings, function (string $query, array $bindings): array {
            $statement = $this->pdo->prepare($query);
            $this->bindValues($statement, $bindings);

            $statement->execute();

            return $statement->fetchAll();
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
        return $this->statement($query, $bindings);
    }

    /**
     * Run a select statement against the database and returns a generator.
     *
     * @param string $query
     * @param array $bindings
     * @return \Generator
     */
    public function cursor($query, array $bindings = []): \Generator
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) {
            // First we will create a statement for the query. Then, we will set the fetch
            // mode and prepare the bindings for the query. Once that's done we will be
            // ready to execute the query against the database and return the cursor.
            $statement = $this->pdo->prepare($query);

            $this->bindValues($statement, $bindings);

            // Next, we'll execute the query against the database and return the statement
            // so we can return the cursor. The cursor will use a PHP generator to give
            // back one row at a time without using a bunch of memory to render them.
            $statement->execute();

            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param string $query
     * @return bool
     */
    public function unprepared(string $query): bool
    {
        return $this->run($query, [], function ($query): bool {
            return (bool)$this->pdo->exec($query);
        });
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function statement(string $query, array $bindings = []): bool
    {
        return $this->run($query, $bindings, function (string $query, array $bindings): bool {
            $statement = $this->pdo->prepare($query);

            $this->bindValues($statement, $bindings);

            return $statement->execute();
        });
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
            $statement = $this->pdo->prepare($query);
            $this->bindValues($statement, $bindings);

            $statement->execute();

            return $statement->rowCount();
        });
    }

    public function disconnect(): void
    {
        $this->pdo = null;
    }

    protected function reconnectIfMissingConnection(): void
    {
        if (null === $this->pdo) {
            $this->reconnect();
        }
    }

    protected function reconnect(): void
    {
        $this->pdo = ($this->reconnector)();
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
        $this->reconnectIfMissingConnection();

        try {
            return $run($query, $bindings);
        } catch (PDOException $e) {
            if ($this->causedByLostConnection($e)) {
                $this->reconnect();

                return $run($query, $bindings);
            }

            throw new QueryException(
                $e->getMessage(),
                (int)$e->getCode(),
                $e->errorInfo[0] ?? '',
                $query,
                $bindings,
                $e
            );
        }
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param \PDOStatement $statement
     * @param array $bindings
     * @return void
     */
    protected function bindValues(\PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}
