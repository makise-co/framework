<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Providers;

use DI\Container;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(EventDispatcherInterface::class, static function () {
            return new EventDispatcher();
        });
    }
}
