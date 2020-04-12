<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Util;

use ReflectionClass;

class TraitsCollector
{
    /**
     * Get all traits that are used in class
     * Performs recursive search of traits
     *
     * @param ReflectionClass $class
     * @return ReflectionClass[] map in format trait name => ReflectionClass
     */
    public static function getTraits(ReflectionClass $class): array
    {
        $traits = [];

        foreach ($class->getTraits() as $trait) {
            $traits[$trait->getName()] = $trait;

            $traits += static::getTraits($trait);
        }

        $reflection = $class->getParentClass();
        if (false !== $reflection) {
            $traits += static::getTraits($reflection);
        }

        return $traits;
    }
}
