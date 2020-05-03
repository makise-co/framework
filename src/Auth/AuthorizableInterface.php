<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth;

interface AuthorizableInterface
{
    /**
     * @param string[] $roles
     * @return bool
     */
    public function hasAllRoles(array $roles): bool;

    /**
     * @param string[] $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool;

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool;

    /**
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool;

    /**
     * @param string[] $roles
     * @return bool
     */
    public function hasAnyRoles(array $roles): bool;

    /**
     * @param string[] $permissions
     * @return bool
     */
    public function hasAnyPermissions(array $permissions): bool;
}
