<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Auth;

interface UserProviderInterface
{
    /**
     * @param string|int|mixed $id
     * @return AuthenticatableInterface|null
     */
    public function retrieveById($id): ?AuthenticatableInterface;

    /**
     * @param array $credentials
     * @return AuthenticatableInterface|null
     */
    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface;
}
