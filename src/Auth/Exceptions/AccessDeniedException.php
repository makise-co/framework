<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth\Exceptions;

use MakiseCo\Http\Exceptions\HttpException;

class AccessDeniedException extends HttpException
{
    protected array $permissions = [];
    protected array $roles = [];

    public function __construct()
    {
        parent::__construct(403, 'access_denied');
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public static function forRoles(array $roles): self
    {
        $self = new self();
        $self->roles = $roles;

        return $self;
    }

    public static function forPermissions(array $permissions): self
    {
        $self = new self();
        $self->permissions = $permissions;

        return $self;
    }
}
