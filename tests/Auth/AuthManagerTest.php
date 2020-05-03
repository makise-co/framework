<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Auth;

use MakiseCo\Auth\AuthManager;
use MakiseCo\Tests\Auth\Http\Stubs\CustomGuard;
use MakiseCo\Tests\Auth\Http\Stubs\CustomUserProvider;
use PHPUnit\Framework\TestCase;

class AuthManagerTest extends TestCase
{
    public function testAddProvider(): void
    {
        $authManager = $this->getAuthManager();

        $authManager->addProvider(
            'some',
            CustomUserProvider::class,
            [
                'cacheTtl' => 120,
            ],
        );

        /* @var CustomUserProvider $provider */
        $provider = $authManager->getProvider('some');

        $this->assertInstanceOf(CustomUserProvider::class, $provider);
        $this->assertEquals(120, $provider->getCacheTtl());
    }

    public function testAddGuard(): void
    {
        $authManager = $this->getAuthManager();

        $authManager->addProvider(
            'some',
            CustomUserProvider::class,
            [
                'cacheTtl' => 120,
            ],
        );
        $authManager->addGuard(
            'sso',
            CustomGuard::class,
            'some',
            [
                'ban' => true,
            ],
        );

        /* @var CustomGuard $guard */
        $guard = $authManager->getGuard('sso');

        $this->assertInstanceOf(CustomGuard::class, $guard);
    }

    protected function getAuthManager(): AuthManager
    {
        $container = (new \DI\ContainerBuilder)->build();

        return new AuthManager($container);
    }
}
