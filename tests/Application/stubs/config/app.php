<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

return [
    'name' => 'Makise-Co',
    'providers' => [
        \MakiseCo\Console\ConsoleServiceProvider::class,
        \MakiseCo\Tests\Application\SomeProvider::class,
    ],
    'commands' => [
        \MakiseCo\Tests\Application\SomeCommand::class,
    ]
];
