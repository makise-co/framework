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

use function property_exists;
use function sprintf;

class CoroPgsqlErrorMaker
{
    public static function make(PostgreSQL $client): PDOException
    {
        $errorInfo = $client->errorInfo ?? ['unknown', -1, $client->error ?? ''];

        $e = new PDOException(static::format($client), $errorInfo[1], null);
        $e->errorInfo = $errorInfo;

        return $e;
    }

    public static function format(PostgreSQL $client): string
    {
        $message = $client->error;

        if (null === $message) {
            return '';
        }

        if (property_exists($client, 'errorInfo')) {
            $errorInfo = $client->errorInfo;

            $message = sprintf(
                'SQLSTATE[%s] %d %s',
                $errorInfo[0],
                $errorInfo[1],
                $message
            );
        }

        return $message;
    }
}
