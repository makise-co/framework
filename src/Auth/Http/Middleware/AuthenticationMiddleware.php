<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Http\Middleware;

use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Auth\AuthManager;
use MakiseCo\Auth\AuthorizableInterface;
use MakiseCo\Auth\Exceptions\UnauthenticatedException;
use MakiseCo\Auth\Guard\GuardInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function is_array;

class AuthenticationMiddleware implements MiddlewareInterface
{
    protected AuthManager $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $guard = $request->getAttribute(GuardInterface::class, null);
        if (null === $guard) {
            throw new \LogicException(sprintf('Missing "%s" attribute', GuardInterface::class));
        }

        $user = $this->tryAuthenticate($request, $guard);
        if (null === $user) {
            throw new UnauthenticatedException();
        }

        // Perhaps it should be replaced to the mutable implementation, because memory allocation is slow
        $request = $request->withAttribute(AuthenticatableInterface::class, $user);

        if ($user instanceof AuthorizableInterface) {
            $request = $request->withAttribute(AuthorizableInterface::class, $user);
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string|string[] $guard - list of guard names to authenticate
     * @return AuthenticatableInterface|null
     */
    protected function tryAuthenticate(ServerRequestInterface $request, $guard): ?AuthenticatableInterface
    {
        if (is_array($guard)) {
            foreach ($guard as $item) {
                $user = $this->authenticate($request, $item);
                if (null !== $user) {
                    return $user;
                }
            }
        }

        return $this->authenticate($request, $guard);
    }

    protected function authenticate(ServerRequestInterface $request, string $guard): ?AuthenticatableInterface
    {
        return $this
            ->authManager
            ->getGuard($guard)
            ->authenticate($request);
    }
}
