<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Auth\Http\Stubs;

use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\Guard\GuardInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthSuccessGuard implements GuardInterface
{
    private bool $isCalled = false;

    public function authenticate(ServerRequestInterface $request): AuthenticatableInterface
    {
        $this->isCalled = true;

        return new class implements AuthenticatableInterface
        {
            public function getAuthIdentifier(): int
            {
                return 1;
            }
        };
    }

    public function isCalled(): bool
    {
        return $this->isCalled;
    }
}
