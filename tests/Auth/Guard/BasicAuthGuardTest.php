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
use MakiseCo\Auth\Guard\BasicAuthGuard;
use MakiseCo\Http\Request;
use MakiseCo\Tests\Auth\Http\Stubs\EmptyUserProvider;
use PHPUnit\Framework\TestCase;

class BasicAuthGuardTest extends TestCase
{
    public function testItWorks(): void
    {
        $mock = $this->createMock(EmptyUserProvider::class);
        $mock
            ->expects($this->once())
            ->method('retrieveByCredentials')
            ->with(['username' => 'username123', 'password' => 'password123'])
            ->willReturn(new class implements AuthenticatableInterface {
                public function getAuthIdentifier(): int
                {
                    return 2;
                }
            });

        $server = [
            'HTTP_AUTHORIZATION' => \base64_encode('username123:password123')
        ];
        $request = new Request([], [], [], [], [], $server, null);

        $guard = new BasicAuthGuard($mock, 'username', 'password');
        $user = $guard->authenticate($request);

        $this->assertNotNull($user);
        $this->assertEquals(2, $user->getAuthIdentifier());
    }
}
