<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database;

use DI\Container;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Database\Connectors\CoroPgsqlConnector;
use MakiseCo\Database\Connectors\MakisePgsqlConnector;
use MakiseCo\Database\Connectors\PdoPgsqlConnector;
use MakiseCo\Postgres\ConnectionConfigBuilder;
use MakiseCo\Postgres\PoolConfig;
use MakiseCo\Providers\ServiceProviderInterface;
use Smf\ConnectionPool\ConnectionPool;

use function array_key_exists;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(
            DatabaseManager::class,
            function (ConfigRepositoryInterface $config) {
                $manager = new DatabaseManager();

                $manager->addConnector('pdo_pgsql', new PdoPgsqlConnector());
                $manager->addConnector('coro_pgsql', new CoroPgsqlConnector());

                foreach ($config->get('database', []) as $name => $dbConfig) {
                    if (\array_key_exists('driver', $dbConfig) && $dbConfig['driver'] === 'makise_pgsql') {
                        $pool = $this->bootMakisePostgresPool($dbConfig);

                        $manager->addPool($name, $pool);
                    } else {
                        $dbConfig['connection']['name'] = $name;

                        $manager->addDatabase($dbConfig);
                    }
                }

                return $manager;
            }
        );
    }

    protected function bootMakisePostgresPool(array $dbConfig): ConnectionPool
    {
        if (!array_key_exists('connection', $dbConfig)) {
            throw new \InvalidArgumentException('Missing "connection" options in config');
        }

        $connectionConfig = (new ConnectionConfigBuilder)
            ->fromArray($dbConfig['connection'])
            ->build();

        $poolConfig = new PoolConfig(
            $dbConfig['pool']['minActive'] ?? 0,
            $dbConfig['pool']['maxActive'] ?? 1,
            $dbConfig['pool']['maxWaitTime'] ?? 6,
            $dbConfig['pool']['maxIdleTime'] ?? 15,
            $dbConfig['pool']['idleCheckInterval'] ?? 30,
        );

        return new ConnectionPool(
            $poolConfig->toArray(),
            new MakisePgsqlConnector($connectionConfig),
            ['connection_config' => $connectionConfig],
        );
    }
}
