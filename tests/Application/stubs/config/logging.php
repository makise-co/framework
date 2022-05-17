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
    [
        'handler' => \MakiseCo\Log\Handler\StreamHandler::class,
        'formatter' => \MakiseCo\Log\Formatter\JsonFormatter::class,
        // parameters passed to the handler constructor
        'handler_with' => [
            'stream' => env('LOG_CHANNEL', 'php://stdout'),
        ],
        // parameters passed to the formatter constructor
        'formatter_with' => [],
    ],
];
