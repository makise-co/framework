<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 *
 */

declare(strict_types=1);

namespace MakiseCo\Testing;

use DI\Container;
use MakiseCo\ApplicationInterface;
use MakiseCo\Bootstrapper;
use MakiseCo\Util\TraitsCollector;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

use function method_exists;

abstract class TestCase extends PHPUnitTestCase
{
    protected ApplicationInterface $app;
    protected Container $container;

    /**
     * @var \ReflectionClass[]
     */
    protected array $traits = [];

    protected function setUp(): void
    {
        $this->app = $this->createApplication();
        $this->container = $this->app->getContainer();

        $this->setUpTraits();
    }

    protected function tearDown(): void
    {
        $this->app->terminate();
        unset($this->app, $this->container);
        \gc_collect_cycles();
    }

    /**
     * Bootstrap your services inside coroutine context
     */
    protected function coroSetUp(): void
    {
    }

    /**
     * Teardown your services inside coroutine context
     */
    protected function coroTearDown(): void
    {
    }

    abstract protected function createApplication(): ApplicationInterface;

    /**
     * Boot the testing helper traits.
     */
    protected function setUpTraits(): void
    {
        $this->traits = TraitsCollector::getTraits(new \ReflectionClass($this));
    }

    protected function bootTraits(): void
    {
        foreach ($this->traits as $trait) {
            $traitName = $trait->getShortName();

            $bootMethod = "boot{$traitName}";
            if (method_exists($this, $bootMethod)) {
                $this->{$bootMethod}();
            }
        }
    }

    protected function cleanupTraits(): void
    {
        foreach ($this->traits as $trait) {
            $traitName = $trait->getShortName();

            $cleanupMethod = "cleanup{$traitName}";
            if (method_exists($this, $cleanupMethod)) {
                try {
                    $this->{$cleanupMethod}();
                } catch (\Throwable $e) {
                    $this->addWarning("Trait {$traitName} cleanup failed");
                    $this->addWarning($e->getMessage());
                }
            }
        }
    }

    /**
     * Run test cases in the coroutine
     *
     * @return mixed|null
     * @throws \Throwable
     */
    protected function runTest()
    {
        $result = null;
        /* @var \Throwable|null $ex */
        $ex = null;

        \Swoole\Coroutine\run(function () use (&$result, &$ex) {
            try {
                $this->bootServices();
                $this->bootTraits();
                $this->coroSetUp();

                $result = parent::runTest();
            } catch (\Throwable $e) {
                $ex = $e;
            }

            try {
                $this->coroTearDown();
            } catch (\Throwable $e) {
                $ex = $e;
            }

            try {
                $this->cleanupTraits();
            } catch (\Throwable $e) {
                $this->addWarning("Unable to cleanup traits: {$e->getMessage()}");
            }

            try {
                $this->stopServices();
            } catch (\Throwable $e) {
                $this->addWarning("Unable to stop services: {$e->getMessage()}");
            }
        });

        if (null !== $ex) {
            throw $ex;
        }

        return $result;
    }

    protected function getServices(): array
    {
        return [];
    }

    protected function bootServices(): void
    {
        /** @var Bootstrapper $bootstrapper */
        $bootstrapper = $this->container->get(Bootstrapper::class);
        $bootstrapper->init($this->getServices());
    }

    protected function stopServices(): void
    {
        /** @var Bootstrapper $bootstrapper */
        $bootstrapper = $this->container->get(Bootstrapper::class);
        $bootstrapper->stop($this->getServices());
    }
}
