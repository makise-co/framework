<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connectors;

use MakiseCo\Database\Connection\Coro\CoroPgsqlConnection;
use Smf\ConnectionPool\Connectors\ConnectorInterface;
use Swoole\Coroutine\PostgreSQL;

use function implode;

class CoroPgsqlConnector implements ConnectorInterface
{
    protected int $connectionsCreated = 0;

    public function connect(array $config): CoroPgsqlConnection
    {
        // add charset to connection
        if (isset($config['charset'])) {
            $config['dsnOptions']['client_encoding'] = $config['charset'];
        }

        // for $config contents look at MakiseCo\Database\Config\PdoPgsqlConnectionConfig::toArray()
        $dsn = $this->getDsn($config);

        $client = $this->makeConnection($dsn, $config);

        $this->connectionsCreated++;

        return new CoroPgsqlConnection(
            $client,
            function (CoroPgsqlConnection $connection) use ($dsn, $config) {
                $connection->setUniqId(\uniqid('', false));

                return $this->makeConnection($dsn, $config);
            },
            $config['name'],
            $this->connectionsCreated,
            \uniqid('', false)
        );
    }

    public function disconnect($connection)
    {
        /* @var CoroPgsqlConnection $connection */

        $connection->disconnect();
    }

    public function isConnected($connection): bool
    {
        /* @var CoroPgsqlConnection $connection */

        return null !== $connection->getClient();
    }

    public function reset($connection, array $config)
    {
    }

    protected function makeConnection(string $dsn, array $config): PostgreSQL
    {
        $client = new PostgreSQL();
        $client->connect($dsn);

        $this->prepareConnection($client, $config);

        return $client;
    }

    public function validate($connection): bool
    {
        return $connection instanceof CoroPgsqlConnection;
    }

    protected function prepareConnection(PostgreSQL $pdo, array $config): void
    {
        $this->setTimezone($pdo, $config);
        $this->setSchema($pdo, $config);
    }

    protected function setSchema(PostgreSQL $pdo, array $config): void
    {
        $schema = $config['schema'] ?? null;
        if (null === $schema) {
            return;
        }

        if (\is_string($schema)) {
            $schema = "\"{$schema}\"";
        } elseif (\is_array($schema)) {
            $schemas = array_map(fn(string $schema) => "\"{$schema}\"", $config['schema']);
            $schema = implode(',', $schemas);
        } else {
            throw new \InvalidArgumentException('Wrong schema value passed');
        }

        $pdo->query("SET search_path TO {$schema}");
    }

    protected function setTimezone(PostgreSQL $pdo, array $config): void
    {
        if (!isset($config['timezone'])) {
            return;
        }

        $timezone = $this->escapeValue($config['timezone']);

        $pdo->query("SET timezone TO {$timezone}");
    }

    protected function getDsn(array $config): string
    {
        // for $config contents look at MakiseCo\Database\Config\PdoConnectionConfig::toArray()
        $parts = [
            'host=' . $config['host'],
            'port=' . $config['port'],
            'user=' . $config['user'],
            'password=' . $config['password'] ?? '',
            'dbname=' . $config['database'],
        ];

        foreach ($config['dsnOptions'] ?? [] as $dsnName => $dsnValue) {
            $parts[] = "{$dsnName}={$this->escapeValue($dsnValue)}";
        }

        return implode(';', $parts);
    }

    protected function escapeValue(string $value): string
    {
        if ('' === $value) {
            return $value;
        }

        $escaped = addcslashes($value, "'\\");

        return "'{$escaped}'";
    }
}
