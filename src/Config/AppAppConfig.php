<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Config;

use MakiseCo\Env\Env;
use MakiseCo\Http\Middleware\CorsMiddleware;

class AppAppConfig implements AppConfigInterface
{
    protected string $directory;
    protected string $name = 'Makise-Co';
    protected string $env = 'dev';
    protected bool $debug = true;
    protected string $url = '';
    protected string $timezone = 'UTC';
    protected string $locale = 'en';

    protected array $providers = [];

    protected array $commands = [];

    protected array $logger = [
        [
            'handler' => \MakiseCo\Log\Handler\StreamHandler::class,
            'formatter' => \MakiseCo\Log\Formatter\JsonFormatter::class,
            'handler_with' => [
                'stream' => 'php://stdout',
            ],
            'formatter_with' => [],
        ]
    ];

    protected SwooleHttpConfig $httpConfig;

    protected array $httpRoutes = [];
    protected array $globalMiddlewares = [
        CorsMiddleware::class,
    ];

    public function __construct(
        string $directory,
        ?string $name,
        ?string $env,
        ?bool $debug,
        ?string $url,
        ?string $timezone,
        ?string $locale
    ) {
        $this->directory = $directory;

        if (null !== $name) {
            $this->name = $name;
        }

        if (null !== $env) {
            $this->env = $env;
        }

        if (null !== $debug) {
            $this->debug = $debug;
        }

        if (null !== $url) {
            $this->url = $url;
        }

        if (null !== $timezone) {
            $this->timezone = $timezone;
        }

        if (null !== $locale) {
            $this->locale = $locale;
        }

        $this->boot();
    }

    protected function boot(): void
    {
        $this->httpConfig = new SwooleHttpConfig(
            (int)Env::env('WORKER_NUM', fn() => \swoole_cpu_num())
        );

        $this->setupDefaultProviders();
        $this->setupDefaultCommands();
        $this->setupDefaultInspiringCommands();

        $this->httpRoutes[] = $this->directory . DIRECTORY_SEPARATOR . 'routes/api.php';
    }

    protected function setupDefaultProviders(): void
    {
        $this->providers[] = \MakiseCo\Log\LoggerServiceProvider::class;
        $this->providers[] = \MakiseCo\Providers\EventDispatcherServiceProvider::class;
        $this->providers[] = \MakiseCo\Console\ConsoleServiceProvider::class;
        $this->providers[] = \MakiseCo\Http\HttpServiceProvider::class;
    }

    protected function setupDefaultCommands(): void
    {
        $this->commands[] = \MakiseCo\Console\Commands\DumpEnvCommand::class;
        $this->commands[] = \MakiseCo\Console\Commands\DumpConfigCommand::class;
        $this->commands[] = \MakiseCo\Console\Commands\RoutesDumpCommand::class;
        $this->commands[] = \MakiseCo\Console\Commands\StartHttpSever::class;
    }

    protected function setupDefaultInspiringCommands(): void
    {
        $this->commands[] = \MakiseCo\Console\Commands\MakiseCommand::class;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEnv(): string
    {
        return $this->env;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getLoggerConfig(): array
    {
        return $this->logger;
    }

    /**
     * @return string[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return string[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getHttpConfig(): SwooleHttpConfig
    {
        return $this->httpConfig;
    }

    /**
     * @return string[]
     */
    public function getHttpRoutes(): array
    {
        return $this->httpRoutes;
    }

    public function getGlobalMiddlewares(): array
    {
        return $this->globalMiddlewares;
    }
}
