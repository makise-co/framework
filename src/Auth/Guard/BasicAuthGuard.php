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
use function base64_decode;
use function explode;

class BasicAuthGuard implements GuardInterface
{
    protected UserProviderInterface $provider;
    protected string $storageUsernameKey;
    protected string $storagePasswordKey;

    public function __construct(
        UserProviderInterface $provider,
        string $storageUsernameKey = 'email',
        string $storagePasswordKey = 'password'
    ) {
        $this->provider = $provider;
        $this->storageUsernameKey = $storageUsernameKey;
        $this->storagePasswordKey = $storagePasswordKey;
    }

    public function authenticate(ServerRequestInterface $request): ?AuthenticatableInterface
    {
        $token = $this->getBasicAuthString($request);
        if (null === $token) {
            return null;
        }

        [$username, $password] = explode(':', $token);

        return $this->provider->retrieveByCredentials([
            $this->storageUsernameKey => $username,
            $this->storagePasswordKey => $password,
        ]);
    }

    protected function getBasicAuthString(ServerRequestInterface $request): ?string
    {
        $token = $request->getHeader('Authorization')[0] ?? null;
        if (null === $token) {
            return null;
        }

        $base64 = base64_decode($token);
        if (false === $base64) {
            return null;
        }

        return $base64;
    }
}
