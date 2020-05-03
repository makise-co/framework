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
use MakiseCo\Auth\UserProviderInterface;

class EmptyUserProvider implements UserProviderInterface
{
    public function retrieveById($id): ?AuthenticatableInterface
    {
        return null;
    }

    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        return null;
    }
}
