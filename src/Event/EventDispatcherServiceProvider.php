<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Event;

use DI\Container;
use MakiseCo\Providers\ServiceProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // event dispatcher should be created immediately
        $dispatcher = new EventDispatcher();

        $container->set(EventDispatcherInterface::class, $dispatcher);

        // alias EventDispatcher to EventDispatcherInterface
        $container->set(EventDispatcher::class, \DI\get(EventDispatcherInterface::class));

        $this->registerEvents($container, $dispatcher);
    }

    protected function registerEvents(Container $container, EventDispatcher $dispatcher): void
    {
    }
}
