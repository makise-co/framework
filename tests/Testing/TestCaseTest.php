<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Testing;

use MakiseCo\Application;
use MakiseCo\ApplicationInterface;
use MakiseCo\Bootstrapper;
use MakiseCo\Testing\TestCase;
use Swoole\Coroutine;

class TestCaseTest extends TestCase
{
    use SomeHelper;

    private bool $serviceBooted = false;
    private bool $serviceStopped = false;

    protected function createApplication(): ApplicationInterface
    {
        $app = new Application(
            dirname(__DIR__) . '/Application/stubs/',
            dirname(__DIR__) . '/Application/stubs/config/'
        );

        $app->getContainer()->get(Bootstrapper::class)->addService(
            'test',
            function () {
                $this->serviceBooted = true;
            },
            function () {
                $this->serviceStopped = true;
            }
        );

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // setUp should run in the coroutine
        self::assertGreaterThan(0, Coroutine::getCid());
    }

    protected function tearDown(): void
    {
        // tearDown should run in the coroutine
        self::assertGreaterThan(0, Coroutine::getCid());

        parent::tearDown();
    }

    public function testTraitBooted(): void
    {
        self::assertTrue($this->someHelperBooted);
    }

    public function testTraitCleanedUp(): void
    {
        Coroutine::defer(function () {
            self::assertTrue($this->someHelperStopped);
        });
    }

    public function testServiceBootstrapped(): void
    {
        self::assertTrue($this->serviceBooted);
    }

    public function testServiceStopped(): void
    {
        Coroutine::defer(function () {
            self::assertTrue($this->serviceStopped);
        });
    }
}
