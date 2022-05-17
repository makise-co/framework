<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Application;

use MakiseCo\Application;
use MakiseCo\Bootstrapper;
use MakiseCo\Config\ConfigRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as ConsoleApplication;

use function MakiseCo\Env\env;

class ApplicationTest extends TestCase
{
    public function testEnvLoaded(): void
    {
        $_ENV['APP_NAME'] = 'MakiseTest';
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $env = env('APP_NAME');

        self::assertEquals('MakiseTest', $env);
    }

    public function testEnvFileLoaded(): void
    {
        file_put_contents(__DIR__ . '/.env', "APP_NAME=Makise-Env");

        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $env = env('APP_NAME');

        try {
            self::assertEquals('Makise-Env', $env);
        } finally {
            unlink(__DIR__ . '/.env');
        }
    }

    public function testEnvFileOverloaded(): void
    {
        file_put_contents(__DIR__ . '/.env', "APP_NAME=Makise-Env");
        file_put_contents(__DIR__ . '/.env.testing', "APP_NAME=Makise-EnvTesting");

        $_ENV['APP_ENV'] = 'testing';

        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $env = env('APP_NAME');

        try {
            self::assertEquals('Makise-EnvTesting', $env);
        } finally {
            unlink(__DIR__ . '/.env');
            unlink(__DIR__ . '/.env.testing');
        }
    }

    public function testEnvFileOverloadedByCliArg(): void
    {
        file_put_contents(__DIR__ . '/.env', "APP_NAME=Makise-Env");
        file_put_contents(__DIR__ . '/.env.testing', "APP_NAME=Makise-EnvTestingCliArg");

        global $argv;
        $argv[] = '--env=testing';

        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $env = env('APP_NAME');

        try {
            self::assertEquals('Makise-EnvTestingCliArg', $env);
        } finally {
            unlink(__DIR__ . '/.env');
            unlink(__DIR__ . '/.env.testing');
        }
    }

    public function testConfigLoaded(): void
    {
        $_ENV['APP_NAME'] = $_SERVER['APP_NAME'] = 'Makise-Co';

        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $val = $app
            ->getContainer()
            ->get(ConfigRepositoryInterface::class)
            ->get('app.name');

        self::assertEquals('Makise-Co', $val);
    }

    public function testProvidersLoaded(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $val = $app
            ->getContainer()
            ->get(ConfigRepositoryInterface::class)
            ->get('some');

        self::assertEquals('it works', $val);
    }

    public function testCommandsLoaded(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $hasCommand = $app
            ->getContainer()
            ->get(ConsoleApplication::class)
            ->has('some');

        self::assertTrue($hasCommand);
    }

    public function testCommandExecuted(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $code = $app->run(['', 'some']);

        self::assertSame(2, $code);
    }

    public function testCommandBootstrapper(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');
        /** @var Bootstrapper $bootstrapper */
        $bootstrapper = $app->getContainer()->get(Bootstrapper::class);

        $initTriggered = false;
        $stopTriggered = false;

        $bootstrapper->addService(
            'test',
            function () use (&$initTriggered) {
                $initTriggered = true;
            },
            function () use (&$stopTriggered) {
                $stopTriggered = true;
            }
        );

        $code = $app->run(['', 'some']);

        self::assertSame(2, $code);
        self::assertTrue($initTriggered);
        self::assertTrue($stopTriggered);
    }
}
