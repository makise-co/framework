<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Application;

use DI\Container;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Providers\ServiceProviderInterface;

class SomeProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container
            ->get(ConfigRepositoryInterface::class)
            ->set('some', 'it works');
    }
}
