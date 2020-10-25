<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Http\Middleware;

use MakiseCo\Auth\AuthorizableInterface;
use MakiseCo\Auth\Exceptions\AccessDeniedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function MakiseCo\Http\Router\Helper\getRouteAttribute;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * Request attribute that holds permissions list
     */
    public const PERMISSIONS = 'permissions';

    /**
     * Request attribute that holds roles list
     */
    public const ROLES = 'roles';

    /**
     * Request attribute that holds roles/permissions match mode
     */
    public const MODE = 'auth_mode';

    /**
     * Authorizable must have all roles/permissions
     */
    public const MODE_ALL = 'all';

    /**
     * Authorizable must have any of roles/permissions
     */
    public const MODE_ANY = 'any';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorizable = $request->getAttribute(AuthorizableInterface::class, null);
        if (!$authorizable instanceof AuthorizableInterface) {
            throw new AccessDeniedException();
        }

        $mode = getRouteAttribute($request, self::MODE, self::MODE_ALL);

        $this->authorizePermissions($request, $authorizable, $mode);
        $this->authorizeRoles($request, $authorizable, $mode);

        return $handler->handle($request);
    }

    protected function authorizePermissions(
        ServerRequestInterface $request,
        AuthorizableInterface $authorizable,
        string $mode
    ): void {
        $permissions = (array)getRouteAttribute($request, self::PERMISSIONS, []);
        if ([] === $permissions) {
            return;
        }

        $authRes = false;

        switch ($mode) {
            case self::MODE_ALL:
                $authRes = $authorizable->hasAllPermissions($permissions);
                break;
            case self::MODE_ANY:
                $authRes = $authorizable->hasAnyPermissions($permissions);
                break;
        }

        if (!$authRes) {
            throw AccessDeniedException::forPermissions($permissions);
        }
    }

    protected function authorizeRoles(
        ServerRequestInterface $request,
        AuthorizableInterface $authorizable,
        string $mode
    ): void {
        $roles = (array)getRouteAttribute($request, self::ROLES, []);
        if ([] === $roles) {
            return;
        }

        $authRes = false;

        switch ($mode) {
            case self::MODE_ALL:
                $authRes = $authorizable->hasAllRoles($roles);
                break;
            case self::MODE_ANY:
                $authRes = $authorizable->hasAnyRoles($roles);
                break;
        }

        if (!$authRes) {
            throw AccessDeniedException::forRoles($roles);
        }
    }
}
