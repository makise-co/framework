<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Database\Connection;

use Closure;
use MakiseCo\Database\Connectors\MakisePgsqlConnector;
use MakiseCo\Database\DatabaseManager;
use MakiseCo\Postgres\ConnectionConfigBuilder;
use MakiseCo\Postgres\Exception\ConnectionException;
use MakiseCo\Postgres\PoolConfig;
use PHPUnit\Framework\TestCase;
use Smf\ConnectionPool\ConnectionPool;
use Swoole\Coroutine;

use function Swoole\Coroutine\run;

class MakisePgsqlConnectionTest extends TestCase
{
    public function testIntegration(): void
    {
        if (!\extension_loaded('pq')) {
            $this->markTestSkipped('pq extenstion is not installed');

            return;
        }

        $this->runCoroutineTestCase(function (DatabaseManager $databaseManager) {
            $connection = $databaseManager->getLazyConnection('test');

            // Select
            $result = $connection->select('SELECT 1 as num');
            $this->assertInstanceOf(\stdClass::class, $result[0]);
            $this->assertSame($result[0]->num, 1);

            // Cursor
            $result = $connection->cursor('SELECT generate_series(1,100) as num');

            $iterations = 0;
            $num = 0;
            foreach ($result as $item) {
                $iterations++;
                $num = $item->num;
            }

            $this->assertSame(100, $iterations);
            $this->assertSame(100, $num);

            // Unprepared
            $result = $connection->unprepared('CREATE TEMP TABLE tbl(num integer, name text)');
            $this->assertTrue($result);

            // Insert
            $result = $connection->insert('INSERT INTO tbl (num, name) VALUES (?, ?), (?, ?)', [
                1,
                'Makise',
                2,
                'Okabe'
            ]);
            $this->assertTrue($result);

            $result = $connection->select('SELECT * FROM tbl ORDER BY num');
            $this->assertCount(2, $result);

            $this->assertSame(1, $result[0]->num);
            $this->assertSame('Makise', $result[0]->name);
            $this->assertSame(2, $result[1]->num);
            $this->assertSame('Okabe', $result[1]->name);

            // Update
            $result = $connection->update('UPDATE tbl SET num = num + 1');
            $this->assertSame(2, $result);

            // Delete
            $result = $connection->delete('DELETE FROM tbl');
            $this->assertSame(2, $result);

            // Transaction
            $connection->transaction(function () use ($connection) {
                $this->assertSame(1, $connection->getTransactionsLevel());

                $connection->insert('INSERT INTO tbl (num, name) VALUES (?, ?), (?, ?)', [
                    1,
                    'Makise',
                    2,
                    'Okabe'
                ]);

                $count = $connection->select('SELECT COUNT(*) FROM tbl')[0]->count;
                $this->assertSame(2, $count);

                $connection->delete('DELETE FROM tbl');
            });

            $count = $connection->select('SELECT COUNT(*) FROM tbl')[0]->count;
            $this->assertSame(0, $count);

            // disconnect during transaction
            $ex = null;
            try {
                $connection->transaction(function () use ($connection) {
                    $connection->disconnect();
                });
            } catch (\Throwable $e) {
                $ex = $e;
            }

            $this->assertInstanceOf(ConnectionException::class, $ex);
            $this->assertSame(0, $connection->getTransactionsLevel());

            // test query not fails after automatic reconnect
            $connection->select('SELECT 1');

            // Nested transactions
            $connection->transaction(function () use ($connection) {
                $this->assertSame(1, $connection->getTransactionsLevel());

                $connection->transaction(function () use ($connection) {
                    $this->assertSame(2, $connection->getTransactionsLevel());
                });

                $this->assertSame(1, $connection->getTransactionsLevel());
            });
        });
    }

    protected function runCoroutineTestCase(Closure $callback): void
    {
        $res = null;

        run(function () use ($callback, &$res) {
            $databaseManager = new DatabaseManager();
            $connectionConfig =
                (new ConnectionConfigBuilder())
                    ->withUnbuffered(false)
                    ->fromArray([
//                        'host' => 'host.docker.internal',
                        'host' => 'postgres',
                        'user' => 'makise',
                        'password' => 'el-psy-congroo',
                        'database' => 'makise',
                    ])
                    ->build();

            $pool = new ConnectionPool(
                (new PoolConfig(1, 1))->toArray(),
                new MakisePgsqlConnector($connectionConfig),
                ['connection_config' => $connectionConfig],
            );
            $pool->init();

            while ($pool->getIdleCount() !== 1) {
                Coroutine::sleep(0.25);
            }

            $databaseManager->addPool('test', $pool);

            try {
                $res = $callback($databaseManager);
            } catch (\Throwable $e) {
                $res = $e;
            }

            $pool->close();
        });

        if ($res instanceof \Throwable) {
            throw $res;
        }
    }
}
