<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connectors;

use MakiseCo\Database\Connection\Pdo\PdoPgsqlConnection;
use PDO;

use function array_map;
use function implode;

class PdoPgsqlConnector extends PdoConnector
{
    public function connect(array $config): PdoPgsqlConnection
    {
        // add charset to connection
        if (isset($config['charset'])) {
            $config['dsnOptions']['client_encoding'] = $config['charset'];
        }

        // for $config contents look at MakiseCo\Database\Config\PdoPgsqlConnectionConfig::toArray()
        $dsn = $this->getDsn('pgsql', $config);

        $pdo = $this->makeConnection($dsn, $config);

        return new PdoPgsqlConnection($pdo, function () use ($dsn, $config) {
            return $this->makeConnection($dsn, $config);
        });
    }

    protected function makeConnection(string $dsn, array $config): PDO
    {
        $pdoOptions = $config['pdoOptions'] ?? null;
        if (null === $pdoOptions) {
            $pdoOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ];
        }

        $pdo = new PDO($dsn, $config['user'], $config['password'], $pdoOptions);

        $this->prepareConnection($pdo, $config);

        return $pdo;
    }

    public function validate($connection): bool
    {
        return $connection instanceof PdoPgsqlConnection;
    }

    protected function prepareConnection(PDO $pdo, array $config): void
    {
        $this->setTimezone($pdo, $config);
        $this->setSchema($pdo, $config);
    }

    protected function setSchema(PDO $pdo, array $config): void
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

        $pdo
            ->prepare("SET search_path TO {$schema}")
            ->execute();
    }

    protected function setTimezone(PDO $pdo, array $config): void
    {
        if (!isset($config['timezone'])) {
            return;
        }

        $timezone = $this->escapeValue($config['timezone']);

        $pdo
            ->prepare("SET timezone TO {$timezone}")
            ->execute();
    }
}
