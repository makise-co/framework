<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Testing\Concerns;

use Spiral\Database\DatabaseManager;

/**
 * Cycle ORM Database Transactions Trait
 */
trait DatabaseTransactions
{
    protected function bootDatabaseTransactions(): void
    {
        /* @var DatabaseManager $db */
        $db = $this->container->get(DatabaseManager::class);

        foreach ($this->connectionsToTransact() as $connection) {
            $db->driver($connection)->beginTransaction();
        }
    }

    protected function cleanupDatabaseTransactions(): void
    {
        /* @var DatabaseManager $db */
        $db = $this->container->get(DatabaseManager::class);

        foreach ($this->connectionsToTransact() as $connection) {
            try {
                $db->driver($connection)->rollbackTransaction();
            } catch (\Throwable $e) {
                $this->addWarning("Unable to ROLLBACK transaction on \"{$connection}\" connection: {$e->getMessage()}");
            }

            try {
                $db->driver($connection)->disconnect();
            } catch (\Throwable $e) {
                $this->addWarning("Unable to disconnect \"{$connection}\" connection: {$e->getMessage()}");
            }
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
