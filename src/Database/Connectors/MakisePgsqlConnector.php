<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connectors;

use MakiseCo\Database\Connection\Coro\MakisePgsqlConnection;
use MakiseCo\Postgres\Connection;
use MakiseCo\Postgres\ConnectionConfig;
use Smf\ConnectionPool\Connectors\ConnectorInterface;

class MakisePgsqlConnector implements ConnectorInterface
{
    private ConnectionConfig $config;

    public function __construct(ConnectionConfig $config)
    {
        $this->config = $config;
    }

    public function connect(array $config)
    {
        $connection = new Connection($this->config);
        $connection->connect();

        return new MakisePgsqlConnection($connection);
    }

    public function disconnect($connection)
    {
        /* @var MakisePgsqlConnection $connection */
        $connection->disconnect();
    }

    public function isConnected($connection): bool
    {
        /* @var MakisePgsqlConnection $connection */
        return $connection->isConnected();
    }

    public function reset($connection, array $config)
    {
    }

    public function validate($connection): bool
    {
        return $connection instanceof MakisePgsqlConnection;
    }
}
