<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

use function MakiseCo\Env\env;

return [
    'name' => env('APP_NAME', 'Makise-Co'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool)env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),

    'providers' => [
        \MakiseCo\Log\LoggerServiceProvider::class,
        \MakiseCo\Event\EventDispatcherServiceProvider::class,
        \MakiseCo\Console\ConsoleServiceProvider::class,
        // App service providers
        \MakiseCo\Tests\Application\SomeProvider::class,
    ],

    'commands' => [
        \MakiseCo\Console\Commands\MakiseCommand::class,
        \MakiseCo\Console\Commands\DumpEnvCommand::class,
        \MakiseCo\Console\Commands\DumpConfigCommand::class,
        \MakiseCo\Tests\Application\SomeCommand::class,
    ],
];
