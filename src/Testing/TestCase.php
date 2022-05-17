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
use ReflectionClass;
use Throwable;

use function gc_collect_cycles;
use function method_exists;

abstract class TestCase extends CoroutineTestCase
{
    protected ApplicationInterface $app;
    protected Container $container;

    /**
     * @var ReflectionClass[]
     */
    protected array $traits = [];

    abstract protected function createApplication(): ApplicationInterface;

    protected function setUp(): void
    {
        $this->app = $this->createApplication();
        $this->container = $this->app->getContainer();

        // setup traits only once
        if (empty($this->traits)) {
            $this->setUpTraits();
        }

        $this->bootServices();
        $this->bootTraits();
    }

    protected function tearDown(): void
    {
        $this->cleanupTraits();
        $this->stopServices();

        $this->app->terminate();
        unset($this->app, $this->container);
        gc_collect_cycles();
    }

    /**
     * Boot the testing helper traits.
     */
    protected function setUpTraits(): void
    {
        $this->traits = TraitsCollector::getTraits(new ReflectionClass($this));
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
                } catch (Throwable $e) {
                    $this->addWarning("Trait {$traitName} cleanup failed");
                    $this->addWarning($e->getMessage());
                }
            }
        }
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

    /**
     * Returns list of services that should be initialized before test case starts and stopped after test case finished
     * @see \MakiseCo\Console\Commands\AbstractCommand::getServices()
     *
     * @return string[]|null[] empty list means that the all services should be initialized/stopped,
     * [null] means that the no services will be initialized/stopped
     */
    protected function getServices(): array
    {
        // booting all services by default
        return [];
    }
}
