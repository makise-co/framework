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

class DumpEnvCommandTest extends TestCase
{
    protected function setUp(): void
    {
        unset($_ENV['APP_NAME'], $_SERVER['APP_NAME']);
        unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
    }

    protected function tearDown(): void
    {
        unset($_ENV['APP_NAME'], $_SERVER['APP_NAME']);
        unset($_ENV['APP_ENV'], $_SERVER['APP_ENV']);
    }

    public function testEnvDump(): void
    {
        $_ENV['APP_NAME'] = $_SERVER['APP_NAME'] = 'EnvDump';

        $app = new Application(__DIR__, dirname(__DIR__) . '/../Application/stubs/config');
        $cmd = $app
            ->getContainer()
            ->get(\Symfony\Component\Console\Application::class)
            ->find('env:dump');

        $tester = new CommandTester($cmd);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertStringContainsString('APP_NAME=EnvDump', $output);
    }

    public function testEnvDumpWithEnvFile(): void
    {
        file_put_contents(__DIR__ . '/.env', 'APP_NAME=EnvDumpFile');

        $app = new Application(__DIR__, dirname(__DIR__) . '/../Application/stubs/config');
        $cmd = $app
            ->getContainer()
            ->get(\Symfony\Component\Console\Application::class)
            ->find('env:dump');

        $tester = new CommandTester($cmd);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        try {
            self::assertStringContainsString('APP_NAME=EnvDumpFile', $output);
        } finally {
            unlink(__DIR__ . '/.env');
        }
    }

    public function testEnvDumpWithEnvFileAndCliArg(): void
    {
        file_put_contents(__DIR__ . '/.env', 'APP_NAME=EnvDumpFile');
        file_put_contents(__DIR__ . '/.env.testing', 'APP_NAME=EnvDumpFileOverload');

        $app = new Application(__DIR__, dirname(__DIR__) . '/../Application/stubs/config');
        $code = $app->run(['makise', 'env:dump', '--env=testing']);

        self::assertSame(0, $code);

        unlink(__DIR__ . '/.env');
        unlink(__DIR__ . '/.env.testing');
    }
}
