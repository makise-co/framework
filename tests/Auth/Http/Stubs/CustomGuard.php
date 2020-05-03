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

class CustomGuard implements GuardInterface
{
    private CustomUserProvider $provider;
    private bool $ban;

    public function __construct(CustomUserProvider $provider, bool $ban)
    {
        $this->provider = $provider;
        $this->ban = $ban;
    }

    public function authenticate(ServerRequestInterface $request): ?AuthenticatableInterface
    {
        return null;
    }
}
