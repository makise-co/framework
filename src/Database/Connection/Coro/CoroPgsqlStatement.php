<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection\Coro;

use MakiseCo\Database\Connection\DetectsLostConnection;
use MakiseCo\Database\QueryBuilder\Pgsql\BindHelper;
use Swoole\Coroutine\PostgreSQL;

class CoroPgsqlStatement
{
    use DetectsLostConnection;

    protected ?PostgreSQL $client;
    protected string $query;

    protected bool $isClosed = false;

    protected string $name;

    protected array $namedParams;

    /**
     * @var resource represents result of statement execution
     */
    public $resource;

    /**
     * CoroPgsqlStatement constructor.
     * @param PostgreSQL $client
     * @param string $name
     * @param string $query
     */
    public function __construct(PostgreSQL $client, string $name, string $query)
    {
        $this->client = $client;
        $this->query = $query;
        $this->name = $name;

        if (!$this->client->prepare($this->name, $this->query)) {
            throw CoroPgsqlErrorMaker::make($client);
        }

        $this->query = BindHelper::parseNamedParams($query, $this->namedParams);
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param string[]|int[] $bindings
     */
    public function execute(array $bindings = []): void
    {
        if ($this->isClosed) {
            throw new \LogicException('Statement is closed');
        }

        $bindings = BindHelper::replaceNamedParams($bindings, $this->namedParams);

        $this->resource = $this->client->execute($this->name, $bindings);

        if (false === $this->resource) {
            $e = CoroPgsqlErrorMaker::make($this->client);

            if ($this->causedByLostConnection($e)) {
                $this->isClosed = true;
                $this->client = null;
            } else {
                $this->close();
            }

            throw $e;
        }
    }

    public function close(): void
    {
        if ($this->isClosed) {
            return;
        }

        $this->isClosed = true;

        $this->client->query('DEALLOCATE ' . $this->name);
        $this->client = null;
    }
}
