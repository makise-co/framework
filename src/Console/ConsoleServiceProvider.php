<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Console;

use DI\Container;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(ConsoleApplication::class, static function (ConfigRepositoryInterface $config) use ($container) {
            $errorListener = new ErrorListener;
            $callback = \Closure::fromCallable([$errorListener, 'onConsoleError']);

            $eventDispatcher = $container->get(EventDispatcher::class);
            $eventDispatcher->addListener(ConsoleEvents::ERROR, $callback);

            $console = new ConsoleApplication($config->get('app.name'));
            $console->setAutoExit(false);
            $console->setDispatcher($eventDispatcher);

            return $console;
        });

    }
}
