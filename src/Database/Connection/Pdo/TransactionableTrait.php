<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection\Pdo;

use Closure;
use MakiseCo\Database\Connection\DetectsLostConnection;
use MakiseCo\Database\Exceptions\QueryException;
use PDOException;
use Throwable;

trait TransactionableTrait
{
    use DetectsLostConnection;

    protected int $transactions = 0;

    public function getTransactionsLevel(): int
    {
        return $this->transactions;
    }

    public function transaction(Closure $executor)
    {
        $this->begin();

        try {
            $value = $executor($this);
        } catch (Throwable $e) {
            throw $this->handleTransactionError($e, '');
        }

        $this->commit();

        return $value;
    }

    public function begin(): void
    {
        $this->pdo->beginTransaction();
        $this->transactions++;
    }

    public function rollback(): void
    {
        try {
            $this->pdo->rollBack();
        } catch (Throwable $e) {
            throw $this->handleTransactionError($e, 'ROLLBACK');
        }
    }

    public function commit(): void
    {
        try {
            $this->pdo->commit();
        } catch (Throwable $e) {
            throw $this->handleTransactionError($e, 'COMMIT');
        }
    }

    /**
     * @param Throwable $e
     * @param string $query
     * @return Throwable
     */
    protected function handleTransactionError(Throwable $e, string $query): Throwable
    {
        if ($e instanceof PDOException) {
            if (!$this->causedByLostConnection($e)) {
                $this->rollback();
                $this->transactions--;
            }

            return new QueryException(
                $e->getMessage(),
                (int)$e->getCode(),
                $e->errorInfo[0] ?? '',
                $query,
                [],
                $e
            );
        }

        $this->transactions = 0;

        return $e;
    }
}
