<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Testing\Concerns;

use MakiseCo\Database\DatabaseManager;

use function property_exists;

/**
 * This trait allows you to run database queries in the dry mode
 * It works properly only when the DB pool size is 1
 */
trait DatabaseTransactions
{
    protected function bootDatabaseTransactions(): void
    {
        /* @var DatabaseManager $db */
        $db = $this->container->get(DatabaseManager::class);

        foreach ($this->connectionsToTransact() as $connection) {
            $pool = $db->getPool($connection);

            /* @var \MakiseCo\Database\Connection\ConnectionInterface $connectionObj */
            $connectionObj = $pool->borrow();
            $connectionObj->begin();

            $pool->return($connectionObj);
        }
    }

    protected function cleanupDatabaseTransactions(): void
    {
        /* @var DatabaseManager $db */
        $db = $this->container->get(DatabaseManager::class);

        foreach ($this->connectionsToTransact() as $connection) {
            $pool = $db->getPool($connection);

            /* @var \MakiseCo\Database\Connection\ConnectionInterface $connectionObj */
            $connectionObj = $pool->borrow();
            $connectionObj->rollback();

            $pool->return($connectionObj);
        }
    }

    /**
     * The database connections that should have transactions.
     *
     * @return string[]
     */
    protected function connectionsToTransact(): array
    {
        return property_exists($this, 'connectionsToTransact') ? $this->connectionsToTransact : [];
    }
}
