<?php
/**
 * File: QueryFactory.php
 * Author: Dmitry K. <dmitry.k@brainex.co>
 * Date: 2020-04-22
 * Copyright (c) 2020
 */

declare(strict_types=1);

namespace MakiseCo\Database\QueryBuilder;

use Aura\SqlQuery\AbstractQuery;

use function class_exists;

class QueryFactory extends \Aura\SqlQuery\QueryFactory
{
    /**
     * @var string[]
     */
    protected array $namespaceLookup;

    /**
     * QueryFactory constructor.
     * @param string $db database type
     * @param string|null $common should use common builder?
     * @param string[] $namespaceLookup namespaces to override standard query builders
     */
    public function __construct(
        string $db,
        ?string $common = null,
        array $namespaceLookup = ['MakiseCo\Database\QueryBuilder']
    ) {
        parent::__construct($db, $common);

        $this->namespaceLookup = $namespaceLookup;
    }

    /**
     *
     * Returns a new query object.
     *
     * @param string $query The query object type.
     *
     * @return AbstractQuery
     *
     */
    protected function newInstance($query): AbstractQuery
    {
        $class = $this->getInstanceClass($query);

        return new $class(
            $this->getQuoter(),
            $this->newSeqBindPrefix()
        );
    }

    protected function getInstanceClass(string $query): string
    {
        if ($this->common) {
            return "Aura\\SqlQuery\\Common";
        }

        foreach ($this->namespaceLookup as $namespace) {
            $path = $namespace . "\\{$this->db}\\{$query}";
            if (class_exists($path)) {
                return $path;
            }
        }

        return "Aura\\SqlQuery\\{$this->db}\\{$query}";
    }
}
