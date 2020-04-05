<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Log;

use DI\Container;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Providers\ServiceProviderInterface;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

class LoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->set(LoggerInterface::class, function (Container $container, ConfigRepositoryInterface $config) {
            $logger = new Logger($config->get('app.name'));

            foreach ($config->get('logging', []) as $handlerConfig) {
                $this->setupMonologHandler($container, $logger, $handlerConfig);
            }

            return $logger;
        });

        // alias monolog to LoggerInterface
        $container->set(Logger::class, \DI\get(LoggerInterface::class));
    }

    protected function setupMonologHandler(Container $container, Logger $logger, array $handlerConfig): void
    {
        $handlerClass = $handlerConfig['handler'];

        $handler = $container->make($handlerClass, $handlerConfig['handler_with'] ?? []);
        if (!$handler instanceof HandlerInterface) {
            throw new \InvalidArgumentException(
                "{$handlerClass} must implement Monolog\Handler\HandlerInterface"
            );
        }

        $formatterClass = $handlerConfig['formatter'] ?? null;
        if ($handler instanceof FormattableHandlerInterface && null !== $formatterClass) {
            $formatter = $container->make($formatterClass, $handlerConfig['formatter_with'] ?? []);
            if (!$formatter instanceof FormatterInterface) {
                throw new \InvalidArgumentException(
                    "{$formatterClass} must implement Monolog\Formatter\FormatterInterface"
                );
            }

            $handler->setFormatter($formatter);
        }

        $logger->pushHandler($handler);
    }
}
