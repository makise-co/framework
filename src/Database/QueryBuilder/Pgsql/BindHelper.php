<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\QueryBuilder\Pgsql;

use function addcslashes;
use function array_map;
use function implode;
use function is_string;

final class BindHelper
{
    public static function array(array $data): string
    {
        $str = implode(',', array_map(static function ($item) {
            if (null === $item) {
                return 'NULL';
            }

            if (is_string($item)) {
                $escaped = addcslashes($item, '"');

                return "\"{$escaped}\"";
            }

            return $item;
        }, $data));

        return "{{$str}}";
    }

    public static function escapeStringForSqlLike(string $str): string
    {
        return addcslashes($str, '%_\\');
    }
}
