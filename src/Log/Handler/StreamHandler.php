<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Log\Handler;

use Monolog\Handler\StreamHandler as BaseStreamHandler;

use function fwrite;
use function str_split;

class StreamHandler extends BaseStreamHandler
{
    /**
     * Write to stream
     * @param resource $stream
     * @param array $record
     */
    protected function streamWrite($stream, array $record): void
    {
        $this->fwriteStream($stream, (string)$record['formatted']);
    }

    /**
     * READ NOTES SECTION - https://www.php.net/manual/en/function.fwrite.php
     *
     * @param resource $stream
     * @param string $string
     */
    protected function fwriteStream($stream, string $string): void
    {
        foreach (str_split($string, 1024) as $chunk) {
            fwrite($stream, $chunk);
        }
    }
}
