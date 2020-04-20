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
     * @return UserInterface
     */
    public function retrieveById($id): UserInterface;

    /**
     * @param array $credentials
     * @return UserInterface
     */
    public function retrieveByCredentials(array $credentials): UserInterface;
}
