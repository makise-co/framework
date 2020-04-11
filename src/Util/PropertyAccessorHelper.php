<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Util;

use function explode;

final class PropertyAccessorHelper
{
    public static function fromDotNotation(string $key): string
    {
        $parts = explode('.', $key);

        $str = '';

        foreach ($parts as $part) {
            if ('.' === $part) {
                continue;
            }

            $str .= "[$part]";
        }

        return $str;
    }
}
