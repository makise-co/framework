<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Config;

use ArrayAccess;
use MakiseCo\Contracts\ArrayableInterface;

/**
 * @extends ArrayAccess<string,mixed>
 */
interface ConfigRepositoryInterface extends ArrayAccess, ArrayableInterface
{
    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void;
}
