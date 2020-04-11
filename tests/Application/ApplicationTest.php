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
use MakiseCo\Config\ConfigRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application as ConsoleApplication;

use function MakiseCo\Env\env;
use function putenv;

class ApplicationTest extends TestCase
{
    public function testEnvLoaded(): void
    {
        putenv('APP_NAME=MakiseTest');
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $env = env('APP_NAME');

        $this->assertEquals('MakiseTest', $env);
    }

    public function testConfigLoaded(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $val = $app
            ->getContainer()
            ->get(ConfigRepositoryInterface::class)
            ->get('app.name');

        $this->assertEquals('Makise-Co', $val);
    }

    public function testProvidersLoaded(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $val = $app
            ->getContainer()
            ->get(ConfigRepositoryInterface::class)
            ->get('some');

        $this->assertEquals('it works', $val);
    }

    public function testCommandsLoaded(): void
    {
        $app = new Application(__DIR__, __DIR__ . '/stubs/config');

        $hasCommand = $app
            ->getContainer()
            ->get(ConsoleApplication::class)
            ->has('some');

        $this->assertTrue($hasCommand);
    }
}
