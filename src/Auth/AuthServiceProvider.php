<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth;

use DI\Container;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Providers\ServiceProviderInterface;
use function array_key_exists;
use function is_array;
use function is_string;

class AuthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = $container->get(ConfigRepositoryInterface::class);

        /* @var AuthManager $authManager */
        $authManager = $container->make(AuthManager::class);
        $container->set(AuthManager::class, $authManager);

        foreach ($config->get('auth.providers') as $name => $params) {
            if (is_array($params) && array_key_exists('class', $params)) {
                $class = $params['class'];
                unset($params['class']);

                $authManager->addProvider($name, $class, $params);
            } elseif (is_string($params)) {
                $authManager->addProvider($name, $params, []);
            } else {
                throw new \InvalidArgumentException("Wrong provider \"$name\" configuration");
            }
        }

        foreach ($config->get('auth.guards', []) as $name => $guard) {
            ['class' => $class, 'provider' => $provider] = $guard;
            unset($guard['class'], $guard['provider']);

            $authManager->addGuard($name, $class, $provider, $guard);
        }
    }
}
