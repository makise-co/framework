<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Database\Connection;

use function strpos;

trait DetectsLostConnection
{
    /**
     * Determine if the given exception was caused by a lost connection.
     * This method was copied from Laravel framework
     *
     * @param  \PDOException  $e
     * @return bool
     */
    protected function causedByLostConnection(\PDOException $e): bool
    {
        $message = $e->getMessage();

        $patterns = [
            'terminating connection',
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'ORA-03114',
            'Packets out of order. Expected',
            'Adaptive Server connection failed',
            'Communication link failure',
            'connection is no longer usable',
            'Login timeout expired',
            'Connection refused',
            'running with the --read-only option so it cannot execute this statement',
            'the database system is starting up',
        ];

        foreach ($patterns as $pattern) {
            if (false !== strpos($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the given exception was caused by a lost connection.
     * This method was copied from Laravel framework
     *
     * @param string $message
     * @return bool
     */
    protected function causedByLostConnectionByString(string $message): bool
    {
        $patterns = [
            'terminating connection',
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'ORA-03114',
            'Packets out of order. Expected',
            'Adaptive Server connection failed',
            'Communication link failure',
            'connection is no longer usable',
            'Login timeout expired',
            'Connection refused',
            'running with the --read-only option so it cannot execute this statement',
            'the database system is starting up',
        ];

        foreach ($patterns as $pattern) {
            if (false !== strpos($message, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
