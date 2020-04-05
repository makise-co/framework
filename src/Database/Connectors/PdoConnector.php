<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connectors;

use MakiseCo\Database\Connection\ConnectionInterface;
use Smf\ConnectionPool\Connectors\ConnectorInterface;

use function implode;
use function addcslashes;

abstract class PdoConnector implements ConnectorInterface
{
    public function disconnect($connection)
    {
        /* @var ConnectionInterface $connection */
        $connection->disconnect();
    }

    public function isConnected($connection): bool
    {
        /* @var ConnectionInterface $connection */
        return null !== $connection->getPdo();
    }

    public function reset($connection, array $config)
    {
    }

    protected function getDsn(string $prefix, array $config): string
    {
        // for $config contents look at MakiseCo\Database\Config\PdoConnectionConfig::toArray()
        $parts = [
            'host=' . $config['host'],
            'port=' . $config['port'],
            'dbname=' . $config['database'],
        ];

        foreach ($config['dsnOptions'] ?? [] as $dsnName => $dsnValue) {
            $parts[] = "{$dsnName}={$this->escapeValue($dsnValue)}";
        }

        return $prefix . ':' . implode(';', $parts);
    }

    protected function escapeValue(string $value): string
    {
        $escaped = addcslashes($value, "'\\");

        return "'{$escaped}'";
    }
}
