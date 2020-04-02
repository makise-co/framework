<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo;

use DI\Container;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use MakiseCo\Config\AppConfigInterface;
use MakiseCo\Env\Env;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;

class Application implements ApplicationInterface
{
    protected bool $isBooted = false;

    protected string $directory;
    protected string $configClass;

    protected Container $container;
    protected AppConfigInterface $config;

    public function __construct(string $directory, string $configClass)
    {
        $this->directory = $directory;
        $this->configClass = $configClass;
        $this->container = (new \DI\ContainerBuilder())->build();

        $this->boot();
    }

    public function run(array $argv): int
    {
        $console = $this->container->get(ConsoleApplication::class);

        return $this->container->call(fn() => $console->run(new ArgvInput($argv)));
    }

    public function terminate(): void
    {
        // TODO: Implement terminate() method.
    }

    public function getConfig(): AppConfigInterface
    {
        return $this->config;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    protected function boot(): void
    {
        if ($this->isBooted) {
            return;
        }

        $this->isBooted = true;

        $this->bootDi();
        $this->bootEnv();
        $this->bootConfig();
        $this->bootProviders();
        $this->bootCommands();
    }

    protected function bootDi(): void
    {
        $this->container->set(ApplicationInterface::class, $this);
    }

    protected function bootEnv(): void
    {
        $repository = RepositoryBuilder::create()
            ->withReaders([new EnvConstAdapter])
            ->withWriters([new EnvConstAdapter, new PutenvAdapter])
            ->make();

        Env::setRepository($repository);

        $dotenv = \Dotenv\Dotenv::create(
            $repository,
            [$this->directory . DIRECTORY_SEPARATOR],
            ['.env'],
            true
        );

        $dotenv->safeLoad();

        // load env-scoped variables
        if (\array_key_exists('APP_ENV', $_ENV)) {
            \Dotenv\Dotenv::create(
                $repository,
                [$this->directory . DIRECTORY_SEPARATOR],
                [".env.{$_ENV['APP_ENV']}"],
                true
            )->safeLoad();
        }
    }

    protected function bootConfig(): void
    {
        $appConfig = $this->container->make($this->configClass, [
            'directory' => $this->directory,
            'name' => Env::env('APP_NAME'),
            'env' => Env::env('APP_ENV'),
            'debug' => Env::env('APP_DEBUG'),
            'url' => Env::env('APP_URL'),
            'timezone' => Env::env('APP_TIMEZONE'),
            'locale' => Env::env('APP_LOCALE'),
        ]);

        $this->container->set(\MakiseCo\Config\AppConfigInterface::class, $appConfig);
        $this->config = $appConfig;
    }

    protected function bootProviders(): void
    {
        $providers = $this->config->getProviders();

        foreach ($providers as $provider) {
            $instance = $this->container->make($provider);

            if (!$instance instanceof ServiceProviderInterface) {
                throw new \InvalidArgumentException("{$provider} must implement ServiceProviderInterface");
            }

            $instance->register($this->container);
        }
    }

    protected function bootCommands(): void
    {
        $console = $this->container->get(ConsoleApplication::class);

        $commands = $this->config->getCommands();

        foreach ($commands as $command) {
            $instance = $this->container->make($command);

            if (!$instance instanceof Command) {
                throw new \InvalidArgumentException(
                    "{$command} must inherit Symfony\Component\Console\Command\Command"
                );
            }

            $console->add($instance);
        }
    }
}
