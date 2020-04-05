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
    'host' => env('HTTP_HOST', '127.0.0.1'),
    'port' => (int)env('HTTP_PORT', 10228),

    'swoole' => [
        'worker_num' => (int)env('HTTP_WORKER_NUM', fn() => \swoole_cpu_num()),
        'reactor_num' => (int)env('HTTP_REACTOR_NUM', fn() => \swoole_cpu_num()),
    ],

    'routes' => [
        __DIR__ . DIRECTORY_SEPARATOR . '../routes/api.php',
    ],

    // global middleware list
    'middleware' => [
//        \MakiseCo\Http\Middleware\CorsMiddleware::class,
    ],
];
