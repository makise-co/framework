<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Guard;

use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\UserProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use function strpos;
use function substr;

class BearerTokenGuard implements GuardInterface
{
    protected UserProviderInterface $provider;
    protected string $storageKey;

    public function __construct(
        UserProviderInterface $provider,
        string $storageKey = 'bearer_token'
    ) {
        $this->provider = $provider;
        $this->storageKey = $storageKey;
    }

    public function authenticate(ServerRequestInterface $request): ?AuthenticatableInterface
    {
        $token = $this->getBearerToken($request);
        if (null === $token) {
            return null;
        }

        return $this->provider->retrieveByCredentials([
            $this->storageKey => $token,
        ]);
    }

    protected function getBearerToken(ServerRequestInterface $request): ?string
    {
        $token = $request->getHeader('Authorization')[0] ?? null;
        if (null === $token) {
            return null;
        }

        if (0 !== strpos($token, 'Bearer ')) {
            return null;
        }

        return substr($token, 7);
    }
}
