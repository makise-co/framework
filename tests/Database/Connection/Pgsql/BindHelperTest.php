<?php

declare(strict_types=1);

namespace MakiseCo\Tests\Database\Connection\Pgsql;

use MakiseCo\Database\QueryBuilder\Pgsql\BindHelper;
use PHPUnit\Framework\TestCase;

class BindHelperTest extends TestCase
{
    public function testNamedParams(): void
    {
        $query = 'SELECT 1 FROM table WHERE id = :id AND name = ? AND kind = :kind AND type = ? AND some = $1';

        $names = [];
        $placeholderQuery = BindHelper::parseNamedParams($query, $names);

        $this->assertEquals(
            'SELECT 1 FROM table WHERE id = $1 AND name = $2 AND kind = $3 AND type = $4 AND some = $5',
            $placeholderQuery
        );

        $bindings = [
            'Makise',
            'Okabe',
            'kind' => 'Kurisu',
            'id' => 228,
            5
        ];

        $newBindings = BindHelper::replaceNamedParams($bindings, $names);

        $this->assertEquals(
            [
                228,
                'Makise',
                'Kurisu',
                'Okabe',
                5,
            ],
            $newBindings
        );
    }

    public function testArray(): void
    {
        $object = new class {
            public function __toString(): string
            {
                return 'object';
            }
        };

        $array = [1, 2, 3, 4.5, true, 'string', $object, null];

        $postgresArray = BindHelper::array($array);

        $this->assertEquals(
            '{1,2,3,4.5,t,"string","object",NULL}',
            $postgresArray
        );
    }
}
