<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\QueryBuilder\Pgsql;

use Aura\SqlQuery\Common\SubselectInterface;

use function array_merge;
use function array_shift;
use function count;
use function implode;
use function preg_split;

trait NativePostgresTrait
{
    /**
     *
     * Rebuilds a condition string, replacing sequential placeholders with
     * named placeholders, and binding the sequential values to the named
     * placeholders.
     *
     * @param string $cond The condition with sequential placeholders.
     *
     * @param array $bind_values The values to bind to the sequential
     * placeholders under their named versions.
     *
     * @return string The rebuilt condition string.
     *
     */
    protected function rebuildCondAndBindValues($cond, array $bind_values): string
    {
        $cond = $this->quoter->quoteNamesIn($cond);

        // bind values against ?-mark placeholders, but because PDO is finicky
        // about the numbering of sequential placeholders, convert each ?-mark
        // to a named placeholder
        $parts = preg_split('/(\?)/', $cond, 0, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $key => $val) {
            if ($val !== '?') {
                continue;
            }

            $bind_value = array_shift($bind_values);
            if ($bind_value instanceof SubselectInterface) {
                $parts[$key] = $bind_value->getStatement();
                $this->bind_values = array_merge(
                    $this->bind_values,
                    $bind_value->getBindValues()
                );
                continue;
            }

            // using native postgres bindings instead of PDO abstracted bindings
            $placeholder = count($this->bind_values) + 1;
            $parts[$key] = '$' . $placeholder;
            $this->bind_values[$placeholder] = $bind_value;
        }

        $cond = implode('', $parts);
        return $cond;
    }
}
