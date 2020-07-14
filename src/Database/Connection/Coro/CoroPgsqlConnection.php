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
use MakiseCo\Database\Connection\Connection;
use MakiseCo\Database\Connection\DetectsLostConnection;
use MakiseCo\Database\Exceptions\QueryException;
use Swoole\Coroutine\PostgreSQL as PgClient;
use Throwable;

class CoroPgsqlConnection extends Connection
{
    use DetectsLostConnection;

    protected int $transactions = 0;

    protected ?PgClient $pgClient;
    protected Closure $reconnector;
    protected string $name;
    protected int $id;
    protected string $uniqId;

    protected bool $shouldReuseStatements = false;

    /**
     * @var CoroPgsqlStatement[]
     */
    protected array $statementsCache = [];

    public function __construct(PgClient $pgClient, Closure $reconnector, string $name, int $id, string $uniqId)
    {
        // accept only valid pgClient instance
        if (null === $pgClient->error) {
            $this->pgClient = $pgClient;
        } else {
            $this->pgClient = null;
        }

        $this->reconnector = $reconnector;
        $this->name = $name;
        $this->id = $id;
        $this->uniqId = $uniqId;
    }

    public function getClient(): ?PgClient
    {
        return $this->pgClient;
    }

    public function setUniqId(string $uniqId): void
    {
        $this->uniqId = $uniqId;
    }

    public function select(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings, function (string $query, array $bindings): array {
            $statement = $this->prepare($query);
            $statement->execute($bindings);

            $result = [];

            while ($res = $this->pgClient->fetchObject($statement->resource)) {
                $result[] = $res;
            }

            return $result;
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

    public function cursor(string $query, array $bindings = []): \Generator
    {
        /* @var CoroPgsqlStatement $statement */
        $statement = $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->prepare($query);
            $statement->execute($bindings);

            return $statement;
        });

        while ($record = $this->pgClient->fetchObject($statement->resource)) {
            yield $record;
        }
    }

    public function getTransactionsLevel(): int
    {
        return $this->transactions;
    }

    public function transaction(Closure $executor)
    {
        $this->begin();

        try {
            $value = $executor($this);

            $this->commit();

            return $value;
        } catch (Throwable $e) {
            $this->rollbackTransactions();

            throw $e;
        }
    }

    public function begin(): void
    {
        if (!$this->pgClient->query('BEGIN')) {
            $this->handleTransactionError('BEGIN');
        }

        $this->transactions++;
    }

    public function rollback(): void
    {
        if (!$this->pgClient->query('ROLLBACK')) {
            $this->handleTransactionError('ROLLBACK');
        }

        $this->transactions--;
    }

    public function commit(): void
    {
        if (!$this->pgClient->query('COMMIT')) {
            $this->handleTransactionError('COMMIT');
        }

        $this->transactions--;
    }

    public function disconnect(): void
    {
        $this->statementsCache = [];
        $this->pgClient = null;
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
            $res = $this->pgClient->query($query);

            if (false === $res) {
                throw (CoroPgsqlErrorMaker::make($this->pgClient));
            }

            return (bool)$res;
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
            $statement = $this->prepare($query);
            $statement->execute($bindings);

            $fetched = $this->pgClient->affectedRows($statement->resource);

            return 0 < $fetched;
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
            $statement = $this->prepare($query);
            $statement->execute($bindings);

            return $this->pgClient->affectedRows($statement->resource);
        });
    }

    protected function reconnect(): void
    {
        $this->statementsCache = [];
        $this->pgClient = null;

        $client = ($this->reconnector)($this);
        if (null !== $client->error) {
            // when swoole postgres client can't connect to server it sets ontimeout
            if ('ontimeout' === $client->error) {
                $client->error = 'Connection refused';
            }

            throw CoroPgsqlErrorMaker::make($client);
        }

        $this->pgClient = $client;
    }

    protected function reconnectIfMissingConnection(): void
    {
        if (null === $this->pgClient) {
            $this->reconnect();
        }
    }

    protected function prepare(string $query): CoroPgsqlStatement
    {
        $stmtName = $this->uniqId . md5($query);

        if ($this->shouldReuseStatements && null !== ($stmt = $this->statementsCache[$stmtName] ?? null)) {
            if ($stmt->isClosed()) {
                $this->statementsCache[$stmtName] = null;
            } else {
                return $stmt;
            }
        }

        $stmt = new CoroPgsqlStatement($this->pgClient, $stmtName, $query);
        if ($this->shouldReuseStatements) {
            $this->statementsCache[$stmtName] = $stmt;
        }

        return $stmt;
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
        } catch (\PDOException $e) {
            if ($this->causedByLostConnection($e)) {
                $this->reconnect();

                return $run($query, $bindings);
            }

            throw ($this->makeQueryException($e, $query, $bindings));
        }
    }

    protected function rollbackTransactions(): void
    {
        while ($this->transactions > 0) {
            // ignore rollback exception, because we will throw query exception
            try {
                $this->rollback();
            } catch (Throwable $e) {
                // connection lost during transaction rollback
                if ($this->causedByLostConnectionByString($e->getMessage())) {
                    $this->transactions = 0;
                    $this->pgClient = null;
                    break;
                }
            }
        }
    }

    protected function handleTransactionError(string $query): void
    {
        $pdoException = CoroPgsqlErrorMaker::make($this->pgClient);
        $exception = $this->makeQueryException($pdoException, $query);

        if ($this->causedByLostConnection($pdoException)) {
            $this->transactions = 0;
            $this->pgClient = null;

            throw $exception;
        }

        $this->rollbackTransactions();

        throw $exception;
    }

    protected function makeQueryException(
        \PDOException $pdoException,
        string $query,
        array $bindings = []
    ): QueryException {
        return new QueryException(
            $pdoException->getMessage(),
            (int)$pdoException->getCode(),
            $query,
            $bindings,
            $pdoException
        );
    }
}
