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

class CustomUserProvider implements UserProviderInterface
{
    private int $cacheTtl;

    public function __construct(int $cacheTtl)
    {
        $this->cacheTtl = $cacheTtl;
    }

    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    public function retrieveById($id): ?AuthenticatableInterface
    {
        return null;
    }

    public function retrieveByCredentials(array $credentials): ?AuthenticatableInterface
    {
        return null;
    }
}
