<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

return [
    [
        'handler' => \MakiseCo\Log\Handler\StreamHandler::class,
        'formatter' => \MakiseCo\Log\Formatter\JsonFormatter::class,
        // parameters passed to handler constructor
        'handler_with' => [
            'stream' => 'php://stdout',
        ],
        // parameters passed to formatter constructor
        'formatter_with' => [],
    ],
];
