<?php
/**
 * File: Insert.php
 * Author: Dmitry K. <dmitry.k@brainex.co>
 * Date: 2020-04-22
 * Copyright (c) 2020
 */

declare(strict_types=1);

namespace MakiseCo\Database\QueryBuilder\Pgsql;

use Aura\SqlQuery\Pgsql\Insert as BaseInsert;

use function count;
use function func_get_args;

class Insert extends BaseInsert
{
    use NativePostgresTrait;

    /**
     *
     * Sets one column value placeholder; if an optional second parameter is
     * passed, that value is bound to the placeholder.
     *
     * @param string $col The column name.
     *
     * @return $this
     *
     */
    protected function addCol($col): self
    {
        $colsCount = count($this->col_values) + 1;

        $key = $this->quoter->quoteName($col);
        // using native postgres bindings instead of PDO abstracted bindings
        $this->col_values[$key] = "\${$colsCount}";

        $args = func_get_args();
        if (count($args) > 1) {
            $this->bindValue($col, $args[1]);
        }

        return $this;
    }
}
