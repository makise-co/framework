<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection;

interface ConnectionInterface
{
    /**
     * @param string $query
     * @param string[]|int[] $bindings
     * @return \stdClass[]
     */
    public function select(string $query, array $bindings = []): array;

    /**
     * @param string $query
     * @param string[]|int[] $bindings
     * @return int
     */
    public function delete(string $query, array $bindings = []): int;

    /**
     * @param string $query
     * @param string[]|int[] $bindings
     * @return int
     */
    public function update(string $query, array $bindings = []): int;

    /**
     * @param string $query
     * @param string[]|int[] $bindings
     * @return bool
     */
    public function insert(string $query, array $bindings = []): bool;

    /**
     * @param string $query
     * @param string[]|int[] $bindings
     * @return \Generator|\stdClass[]
     */
    public function cursor(string $query, array $bindings = []): \Generator;
    public function unprepared(string $query): bool;

    public function begin(): void;
    public function commit(): void;
    public function rollback(): void;

    /**
     * @param \Closure $executor
     * @return mixed
     */
    public function transaction(\Closure $executor);
    public function getTransactionsLevel(): int;

    public function disconnect(): void;
}
