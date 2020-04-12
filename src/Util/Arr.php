<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Util;

use function array_keys;
use function is_array;
use function ksort;
use function sort;

class Arr
{
    /**
     * Recursively sort an array by keys and values.
     *
     * @param array $array
     * @return array
     */
    public static function sortRecursive($array): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }

        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }

        return $array;
    }

    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param array $array
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * Merges multiple arrays, recursively, and returns the merged array.
     *
     * @param bool $preserveIntegerKeys
     * @param array ...$arrays
     * @return array
     */
    public static function mergeRecursive(bool $preserveIntegerKeys, array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                // Renumber integer keys as array_merge_recursive() does unless
                // $preserveIntegerKeys is set to TRUE. Note that PHP automatically
                // converts array keys that are integer strings (e.g., '1') to integers.
                if (is_int($key) && !$preserveIntegerKeys) {
                    $result[] = $value;
                } elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = static::mergeRecursive($preserveIntegerKeys, $result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
