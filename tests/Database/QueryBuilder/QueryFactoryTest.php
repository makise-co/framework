<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Database\QueryBuilder;

use MakiseCo\Database\QueryBuilder\Pgsql\Delete;
use MakiseCo\Database\QueryBuilder\Pgsql\Insert;
use MakiseCo\Database\QueryBuilder\Pgsql\Select;
use MakiseCo\Database\QueryBuilder\Pgsql\Update;
use MakiseCo\Database\QueryBuilder\QueryFactory;
use PHPUnit\Framework\TestCase;

class QueryFactoryTest extends TestCase
{
    public function testOverridingWorks(): void
    {
        $factory = new QueryFactory('pgsql');

        $this->assertInstanceOf(Insert::class, $factory->newInsert());
        $this->assertInstanceOf(Select::class, $factory->newSelect());
        $this->assertInstanceOf(Delete::class, $factory->newDelete());
        $this->assertInstanceOf(Update::class, $factory->newUpdate());
    }
}
