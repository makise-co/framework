<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Auth\Http\Middleware;

use MakiseCo\Auth\AuthorizableInterface;
use MakiseCo\Auth\Exceptions\AccessDeniedException;
use MakiseCo\Auth\Http\Middleware\AuthorizationMiddleware;
use MakiseCo\Http\Request;
use MakiseCo\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthorizationMiddlewareTest extends TestCase
{
    /**
     * @param bool $authModeAll
     *
     * @doesNotPerformAssertions
     * @testWith [true]
     * [false]
     */
    public function testAuthorizationByPermissionsSuccess(bool $authModeAll): void
    {
        $middleware = new AuthorizationMiddleware();

        $request = new Request();
        $request->attributes->set(AuthorizableInterface::class, $this->getAuthorizableByPermissions($authModeAll));
        $request->attributes->set('test', $this);
        $request->attributes->set('permissions', ['test']);
        $request->attributes->set(
            'auth_mode',
            $authModeAll ? AuthorizationMiddleware::MODE_ALL : AuthorizationMiddleware::MODE_ANY
        );

        $middleware->process($request, $this->getEmptyHandler());
    }

    /**
     * @param bool $authModeAll
     *
     * @testWith [true]
     * [false]
     */
    public function testAuthorizationByPermissionsFailed(bool $authModeAll): void
    {
        $middleware = new AuthorizationMiddleware();

        $request = new Request();
        $request->attributes->set(AuthorizableInterface::class, $this->getAuthorizableByPermissions($authModeAll));
        $request->attributes->set('test', $this);
        $request->attributes->set('permissions', ['bad']);
        $request->attributes->set(
            'auth_mode',
            $authModeAll ? AuthorizationMiddleware::MODE_ALL : AuthorizationMiddleware::MODE_ANY
        );

        $this->expectException(AccessDeniedException::class);
        $middleware->process($request, $this->getEmptyHandler());
    }

    /**
     * @param bool $authModeAll
     *
     * @doesNotPerformAssertions
     * @testWith [true]
     * [false]
     */
    public function testAuthorizationByRolesSuccess(bool $authModeAll): void
    {
        $middleware = new AuthorizationMiddleware();

        $request = new Request();
        $request->attributes->set(AuthorizableInterface::class, $this->getAuthorizableByRoles($authModeAll));
        $request->attributes->set('test', $this);
        $request->attributes->set('roles', ['test']);
        $request->attributes->set(
            'auth_mode',
            $authModeAll ? AuthorizationMiddleware::MODE_ALL : AuthorizationMiddleware::MODE_ANY
        );

        $middleware->process($request, $this->getEmptyHandler());
    }

    /**
     * @param bool $authModeAll
     *
     * @testWith [true]
     * [false]
     */
    public function testAuthorizationByRolesFailed(bool $authModeAll): void
    {
        $middleware = new AuthorizationMiddleware();

        $request = new Request();
        $request->attributes->set(AuthorizableInterface::class, $this->getAuthorizableByRoles($authModeAll));
        $request->attributes->set('test', $this);
        $request->attributes->set('roles', ['bad']);
        $request->attributes->set(
            'auth_mode',
            $authModeAll ? AuthorizationMiddleware::MODE_ALL : AuthorizationMiddleware::MODE_ANY
        );

        $this->expectException(AccessDeniedException::class);
        $middleware->process($request, $this->getEmptyHandler());
    }

    protected function getEmptyHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }

    protected function getAuthorizableByPermissions(bool $all): AuthorizableInterface
    {
        return new class($all) implements AuthorizableInterface {
            private bool $all;

            public function __construct(bool $all)
            {
                $this->all = $all;
            }

            public function hasAllRoles(array $roles): bool
            {
                return false;
            }

            public function hasAllPermissions(array $permissions): bool
            {
                if (!$this->all) {
                    return false;
                }

                return ['test'] === $permissions;
            }

            public function hasRole(string $role): bool
            {
                return false;
            }

            public function hasPermission(string $permission): bool
            {
                return false;
            }

            public function hasAnyRoles(array $roles): bool
            {
                return false;
            }

            public function hasAnyPermissions(array $permissions): bool
            {
                if (!$this->all) {
                    return ['test'] === $permissions;
                }

                return false;
            }
        };
    }

    protected function getAuthorizableByRoles(bool $all): AuthorizableInterface
    {
        return new class($all) implements AuthorizableInterface {
            private bool $all;

            public function __construct(bool $all)
            {
                $this->all = $all;
            }

            public function hasAllRoles(array $roles): bool
            {
                if (!$this->all) {
                    return false;
                }

                return ['test'] === $roles;
            }

            public function hasAllPermissions(array $permissions): bool
            {
                return false;
            }

            public function hasRole(string $role): bool
            {
                return false;
            }

            public function hasPermission(string $permission): bool
            {
                return false;
            }

            public function hasAnyRoles(array $roles): bool
            {
                if (!$this->all) {
                    return ['test'] === $roles;
                }

                return false;
            }

            public function hasAnyPermissions(array $permissions): bool
            {
                return false;
            }
        };
    }
}
