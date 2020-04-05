<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Env;

use Dotenv\Repository\RepositoryInterface;
use PhpOption\Option;

/**
 * Env access helper
 * Code was copied from Laravel Support package
 */
class Env
{
    private static RepositoryInterface $repository;

    public static function setRepository(RepositoryInterface $repository): void
    {
        static::$repository = $repository;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        /** @noinspection PhpParamsInspection */
        return Option::fromValue(static::$repository->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return null;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return $default instanceof \Closure ? $default() : $default;
            });
    }
}
