<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Database\Connection;

use MakiseCo\Database\Connection\Connection;
use MakiseCo\Database\Connection\LazyConnection;
use PHPUnit\Framework\TestCase;
use Smf\ConnectionPool\ConnectionPool;

class LazyConnectionTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ConnectionPool
     */
    protected ConnectionPool $pool;

    /**
     * @var Connection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected Connection $connection;

    protected $buffer;

    protected function setUp(): void
    {
        $this->pool = $this->createMock(ConnectionPool::class);
        $this->connection = $this->createMock(Connection::class);
    }

    public function testConnectionReturnedOnDestructInTransaction(): void
    {
        $this
            ->pool
            ->expects($this->once())
            ->method('borrow')
            ->willReturn($this->connection);

        $this
            ->pool
            ->expects($this->once())
            ->method('return')
            ->with($this->connection);

        // connection is still in transaction
        $this
            ->connection
            ->method('getTransactionsLevel')
            ->willReturn(1);

        $lazyConnection = new LazyConnection($this->pool);
        $lazyConnection->select('1');
        $lazyConnection = null;
    }

    public function testConnectionNotReturnedInTransaction(): void
    {
        $this
            ->pool
            ->expects($this->once())
            ->method('borrow')
            ->willReturn($this->connection);

        $this
            ->pool
            ->expects($this->never())
            ->method('return');

        // connection is still in transaction
        $this
            ->connection
            ->method('getTransactionsLevel')
            ->willReturn(1);

        $lazyConnection = new LazyConnection($this->pool);
        $lazyConnection->select('1');

        // save pointer to lazy connection to prevent destructing
        $this->buffer = $lazyConnection;
    }
}
