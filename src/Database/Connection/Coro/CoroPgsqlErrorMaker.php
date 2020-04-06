<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection\Coro;

use Swoole\Coroutine\PostgreSQL;
use PDOException;

use function sprintf;

class CoroPgsqlErrorMaker
{
    public static function make(PostgreSQL $client): PDOException
    {
        $errorInfo = $client->errorInfo ?? ['unknown', -1, $client->error ?? ''];

        $e = new PDOException(static::format($client, $errorInfo), $errorInfo[1], null);
        $e->errorInfo = $errorInfo;

        return $e;
    }

    protected static function format(PostgreSQL $client, array $errorInfo): string
    {
        $message = $client->error;

        if (null === $message) {
            return '';
        }

        $message = sprintf(
            'SQLSTATE[%s] %d %s',
            $errorInfo[0],
            $errorInfo[1],
            $message
        );

        return $message;
    }
}
