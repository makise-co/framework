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

trait InteractsWithDatabase
{
    protected function bootInteractsWithDatabase(): void
    {
        /* @var DatabaseManager $db */
        $db = $this->container->get(DatabaseManager::class);
        $db->initPools();
    }

    protected function cleanupInteractsWithDatabase(): void
    {
        /* @var DatabaseManager $db */
        $db = $this->container->get(DatabaseManager::class);
        $db->closePools();
    }
}
