<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Console\Commands;

use MakiseCo\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DumpConfigCommandTest extends TestCase
{
    public function testConfigDump(): void
    {
        $app = new Application(__DIR__, dirname(__DIR__) . '/../Application/stubs/config');
        $cmd = $app
            ->getContainer()
            ->get(\Symfony\Component\Console\Application::class)
            ->find('config:dump');

        $tester = new CommandTester($cmd);
        $tester->execute([]);
    }
}
