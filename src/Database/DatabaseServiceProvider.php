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
use MakiseCo\Database\Connectors\PdoPgsqlConnector;
use MakiseCo\Providers\ServiceProviderInterface;

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
                    $dbConfig['connection']['name'] = $name;

                    $manager->addDatabase($dbConfig);
                }

                return $manager;
            }
        );
    }
}
