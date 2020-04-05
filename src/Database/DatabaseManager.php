<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database;

use MakiseCo\Database\Connection\LazyConnection;
use MakiseCo\Database\Exceptions\ConnectorNotFoundException;
use MakiseCo\Database\Exceptions\PoolNotFoundException;
use MakiseCo\Disposable\DisposableInterface;
use Smf\ConnectionPool\ConnectionPool;
use Smf\ConnectionPool\Connectors\ConnectorInterface;

class DatabaseManager implements DisposableInterface
{
    /**
     * Key - database config name
     * Value - ConnectionPool
     *
     * @var ConnectionPool[]
     */
    protected array $pools = [];

    /**
     * Key - driver name
     * Value - ConnectorInterface
     *
     * @var ConnectorInterface[]
     */
    protected array $connectors = [];

    public function getPool(string $poolName): ConnectionPool
    {
        $pool = $this->pools[$poolName] ?? null;
        if (null === $pool) {
            throw new PoolNotFoundException($poolName);
        }

        return $pool;
    }

    public function getLazyConnection(string $poolName): LazyConnection
    {
        $pool = $this->getPool($poolName);

        return new LazyConnection($pool);
    }

    /**
     * Create pool for passed database configuration
     * Look at config/database.php for options
     *
     * @param array<string,mixed> $config
     */
    public function addDatabase(array $config): void
    {
        // it comes from config/database.php (keys of assoc array) and DatabaseServiceProvider
        $name = $config['connection']['name'];

        $driver = $config['driver'];
        $connector = $this->connectors[$driver] ?? null;
        if (null === $connector) {
            throw new ConnectorNotFoundException($driver);
        }

        $pool = new ConnectionPool(
            $config['pool'],
            $connector,
            $config['connection']
        );

        $this->pools[$name] = $pool;
    }

    public function addConnector(string $driver, ConnectorInterface $connector): void
    {
        $this->connectors[$driver] = $connector;
    }

    public function initPools(): void
    {
        foreach ($this->pools as $pool) {
            $pool->init();
        }
    }

    public function initPool(string $poolName): void
    {
        $pool = $this->getPool($poolName);
        $pool->init();
    }

    public function closePools(): void
    {
        foreach ($this->pools as $pool) {
            $pool->close();
        }
    }

    public function closePool(string $poolName): void
    {
        $pool = $this->getPool($poolName);
        $pool->close();
    }

    public function dispose(): void
    {
       $this->closePools();
    }
}
