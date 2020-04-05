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
    'pgsql' => [
        'driver' => 'coro_pgsql',
        'connection' => [
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int)env('DB_PORT', 5432),
            'user' => env('DB_USERNAME', 'makise'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE', 'makise'),
            'charset' => 'utf8',
            'schema' => 'public',
            'dsnOptions' => [
                'application_name' => 'Makise-Co',
            ],
        ],
        'pool' => [
            'minActive' => (int)env('DB_POOL_MIN_ACTIVE', 0),
            'maxActive' => (int)env('DB_POOL_MAX_ACTIVE', 1),
            'maxWaitTime' => 5.0,
            'maxIdleTime' => 30.0,
            'idleCheckInterval' => 15.0,
        ],
    ]
];
