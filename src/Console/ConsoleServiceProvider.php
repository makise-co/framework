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
use MakiseCo\ApplicationInterface;
use MakiseCo\Bootstrapper;
use MakiseCo\Config\ConfigRepositoryInterface as Config;
use MakiseCo\Console\Commands\AbstractCommand;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(ConsoleApplication::class, static function (Container $container, Config $config) {
            $eventDispatcher = $container->get(EventDispatcher::class);
            $bootstrapper = $container->get(Bootstrapper::class);

            // add error listener
            $errorListener = new ErrorListener;

            $eventDispatcher->addListener(
                ConsoleEvents::ERROR,
                \Closure::fromCallable([$errorListener, 'onConsoleError'])
            );

            // initialize command dependencies
            $eventDispatcher->addListener(
                ConsoleEvents::COMMAND,
                static function (ConsoleCommandEvent $event) use ($bootstrapper) {
                    $cmd = $event->getCommand();
                    if ($cmd instanceof AbstractCommand) {
                        $bootstrapper->init($cmd->getServices());
                    }
                }
            );

            // stop command dependencies
            $eventDispatcher->addListener(
                ConsoleEvents::TERMINATE,
                static function (ConsoleTerminateEvent $event) use ($bootstrapper) {
                    $cmd = $event->getCommand();
                    if ($cmd instanceof AbstractCommand) {
                        $bootstrapper->stop($cmd->getServices());
                    }
                }
            );

            $console = new Application(
                $container->get(ApplicationInterface::class),
                $config->get('app.name', 'Makise-Co'),
                $container->get(ApplicationInterface::class)->getVersion(),
            );
            $console->setAutoExit(false);
            $console->setDispatcher($eventDispatcher);

            return $console;
        });

        // alias
        $container->set(Application::class, \DI\get(ConsoleApplication::class));
    }
}
