<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Auth\Guard;

use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\Guard\BearerTokenGuard;
use MakiseCo\Http\Request;
use MakiseCo\Tests\Auth\Http\Stubs\EmptyUserProvider;
use PHPUnit\Framework\TestCase;

class BearerTokenGuardTest extends TestCase
{
    public function testItWorks(): void
    {
        $mock = $this->createMock(EmptyUserProvider::class);
        $mock
            ->expects($this->once())
            ->method('retrieveByCredentials')
            ->with(['bearer_token' => 'someSmartToken123'])
            ->willReturn(new class implements AuthenticatableInterface {
                public function getAuthIdentifier(): int
                {
                    return 2;
                }
            });

        $server = [
            'HTTP_AUTHORIZATION' => 'Bearer someSmartToken123'
        ];
        $request = new Request([], [], [], [], [], $server, null);

        $guard = new BearerTokenGuard($mock, 'bearer_token');
        $user = $guard->authenticate($request);

        $this->assertNotNull($user);
        $this->assertEquals(2, $user->getAuthIdentifier());
    }
}
