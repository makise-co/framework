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
use MakiseCo\Auth\Guard\GuardInterface;
use function sprintf;

class AuthManager
{
    protected Container $container;

    /**
     * @var UserProviderInterface[]
     */
    protected array $providers = [];

    /**
     * @var GuardInterface[]
     */
    protected array $guards = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addProvider(string $name, string $class, array $params): void
    {
        $provider = $this->container->make($class, $params);
        if (!$provider instanceof UserProviderInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Wrong user provider "%s" - %s is not an instance of %s',
                    $name,
                    $class,
                    UserProviderInterface::class,
                )
            );
        }

        $this->providers[$name] = $provider;
    }

    public function addGuard(string $name, string $class, string $provider, array $params): void
    {
        $providerInstance = $this->providers[$provider] ?? null;
        if (null === $providerInstance) {
            throw new \InvalidArgumentException(
                sprintf(
                    'User provider "%s" - not found for guard %s',
                    $provider,
                    $name
                )
            );
        }

        $args = ['provider' => $providerInstance];
        $args += $params;

        $guard = $this->container->make($class, $args);
        if (!$guard instanceof GuardInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Wrong guard "%s" - %s is not an instance of %s',
                    $name,
                    $class,
                    GuardInterface::class,
                )
            );
        }

        $this->guards[$name] = $guard;
    }

    public function getProvider(string $name): UserProviderInterface
    {
        $provider = $this->providers[$name] ?? null;
        if (null === $provider) {
            throw new \InvalidArgumentException(sprintf('Provider %s not found', $name));
        }

        return $provider;
    }

    public function getGuard(string $name): GuardInterface
    {
        $guard = $this->guards[$name] ?? null;
        if (null === $guard) {
            throw new \InvalidArgumentException(sprintf('Guard %s not found', $name));
        }

        return $guard;
    }
}
