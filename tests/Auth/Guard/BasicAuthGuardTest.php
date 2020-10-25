<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Auth\Guard;

use Laminas\Diactoros\ServerRequest;
use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\Guard\BasicAuthGuard;
use MakiseCo\Http\FakeStream;
use MakiseCo\Tests\Auth\Http\Stubs\EmptyUserProvider;
use PHPUnit\Framework\TestCase;

class BasicAuthGuardTest extends TestCase
{
    public function testItWorks(): void
    {
        $mock = $this->createMock(EmptyUserProvider::class);
        $mock
            ->expects(self::once())
            ->method('retrieveByCredentials')
            ->with(['username' => 'username123', 'password' => 'password123'])
            ->willReturn(new class implements AuthenticatableInterface {
                public function getAuthIdentifier(): int
                {
                    return 2;
                }
            });

        $request = new ServerRequest(
            [],
            [],
            '/',
            'GET',
            new FakeStream(''),
            ['Authorization' => \base64_encode('username123:password123')]
        );

        $guard = new BasicAuthGuard($mock, 'username', 'password');
        $user = $guard->authenticate($request);

        self::assertNotNull($user);
        self::assertEquals(2, $user->getAuthIdentifier());
    }
}
