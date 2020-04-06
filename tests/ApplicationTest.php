<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests;

use MakiseCo\Application;
use PHPUnit\Framework\TestCase;

use function MakiseCo\Env\env;
use function putenv;

class ApplicationTest extends TestCase
{
    public function testEnvLoaded(): void
    {
        putenv('APP_NAME=MakiseTest');
        $app = new Application(__DIR__, __DIR__ . '/config');

        $env = env('APP_NAME');

        $this->assertEquals('MakiseTest', $env);
    }
}
