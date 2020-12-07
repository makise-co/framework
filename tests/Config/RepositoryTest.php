<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Config;

use MakiseCo\Config\Repository;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{
    public function testRead(): void
    {
        $repo = new Repository([
            'app' => ['name' => 'Makise']
        ]);

        self::assertEquals(
            'Makise',
            $repo->get('app.name')
        );
    }

    public function testReadArray(): void
    {
        $repo = new Repository([
            'app' => ['name' => 'Makise']
        ]);

        self::assertEquals(
            'Makise',
            $repo->get('app')['name']
        );
    }

    public function testWrite(): void
    {
        $repo = new Repository();

        $repo->set('app.name', 'Makise');

        $items = $repo->toArray();

        self::assertEquals('Makise', $items['app']['name']);
    }

    public function testWriteArray(): void
    {
        $repo = new Repository();

        $repo->set('app', ['name' => 'Makise']);

        $items = $repo->toArray();

        self::assertEquals('Makise', $items['app']['name']);
    }

    public function testUnset(): void
    {
        $repo = new Repository();

        $repo->set('app.name', 'Makise');
        unset($repo['app.name']);

        self::assertNull($repo->get('app.name'));
    }

    public function testUnsetArray(): void
    {
        $repo = new Repository([
            'app' => ['name' => 'Makise']
        ]);

        $repo->set('app.name', 'Makise');
        unset($repo['app']);

        self::assertNull($repo->get('app'));
    }
}
