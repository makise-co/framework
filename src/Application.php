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
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Config\Repository;
use MakiseCo\Env\Env;
use MakiseCo\Providers\ServiceProviderInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Finder\Finder;

class Application implements ApplicationInterface
{
    protected bool $isBooted = false;

    protected string $appDir;
    protected string $configDir;

    protected Container $container;

    public function __construct(string $appDir, string $configDir)
    {
        $this->appDir = $appDir;
        $this->configDir = $configDir;

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

    public function getAppDir(): string
    {
        return $this->appDir;
    }

    public function getConfigDir(): string
    {
        return $this->configDir;
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
        // alias to ApplicationInterface
        $this->container->set(self::class, \DI\get(ApplicationInterface::class));
    }

    protected function bootEnv(): void
    {
        $repository = RepositoryBuilder::create()
            ->withReaders([new EnvConstAdapter, new PutenvAdapter])
            ->withWriters([new EnvConstAdapter, new PutenvAdapter])
            ->make();

        Env::setRepository($repository);

        $dotenv = \Dotenv\Dotenv::create(
            $repository,
            [$this->appDir . DIRECTORY_SEPARATOR],
            ['.env'],
            true
        );

        $dotenv->safeLoad();

        // load env-scoped variables
        if (\array_key_exists('APP_ENV', $_ENV)) {
            \Dotenv\Dotenv::create(
                $repository,
                [$this->appDir . DIRECTORY_SEPARATOR],
                [".env.{$_ENV['APP_ENV']}"],
                true
            )->safeLoad();
        }
    }

    protected function bootConfig(): void
    {
        $finder = new Finder();
        $repository = new Repository();

        $this->container->set(ConfigRepositoryInterface::class, $repository);

        $configFiles = $finder
            ->files()
            ->in($this->configDir)
            ->filter(static function (\SplFileInfo $info) {
                return 'php' === $info->getExtension();
            })
            ->getIterator();

        foreach ($configFiles as $configFile) {
            /** @noinspection PhpIncludeInspection */
            $config = include $configFile->getPathname();
            if (!is_array($config)) {
                throw new \InvalidArgumentException("Config file {$configFile->getFilename()} must return array");
            }

            $name = $configFile->getBasename('.php');

            $repository->set($name, $config);
        }

        date_default_timezone_set($repository->get('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');
    }

    protected function bootProviders(): void
    {
        $providers = $this->container
            ->get(ConfigRepositoryInterface::class)
            ->get('app.providers', []);

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

        $commands = $this->container
            ->get(ConfigRepositoryInterface::class)
            ->get('app.commands', []);

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

    public function getVersion(): string
    {
        return '0.0.6';
    }
}
