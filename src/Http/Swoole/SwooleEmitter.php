<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Swoole;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

use function array_key_exists;
use function gmdate;

final class SwooleEmitter
{
    /**
     * @see https://www.swoole.co.uk/docs/modules/swoole-http-server/methods-properties#swoole-http-response-write
     */
    private const CHUNK_SIZE = 8192; // 8 KB

    /**
     * Emits a response for the Swoole environment.
     *
     * @param SwooleResponse $swooleResponse
     * @param ResponseInterface $makiseResponse
     */
    public function emit(SwooleResponse $swooleResponse, ResponseInterface $makiseResponse): void
    {
        $this->emitStatusCode($swooleResponse, $makiseResponse);
        $this->emitHeaders($swooleResponse, $makiseResponse);
        $this->emitBody($swooleResponse, $makiseResponse);
    }

    /**
     * Emit the status code
     *
     * @param SwooleResponse $swooleResponse
     * @param ResponseInterface $makiseResponse
     */
    private function emitStatusCode(SwooleResponse $swooleResponse, ResponseInterface $makiseResponse): void
    {
        $swooleResponse->status($makiseResponse->getStatusCode());
    }

    /**
     * Emit the headers
     *
     * @param SwooleResponse $swooleResponse
     * @param ResponseInterface $makiseResponse
     */
    private function emitHeaders(SwooleResponse $swooleResponse, ResponseInterface $makiseResponse): void
    {
        $headers = $makiseResponse->getHeaders();

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!array_key_exists('Date', $headers)) {
            $date = gmdate('D, d M Y H:i:s GMT');
            $headers['Date'] = [$date];
        }

        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }
    }

    /**
     * Emit the message body.
     *
     * @param SwooleResponse $swooleResponse
     * @param ResponseInterface $makiseResponse
     */
    private function emitBody(SwooleResponse $swooleResponse, ResponseInterface $makiseResponse): void
    {
        $body = $makiseResponse->getBody();

        $body->rewind();

        while (!$body->eof()) {
            $chunk = $body->read(static::CHUNK_SIZE);
            if (empty($chunk)) {
                break;
            }

            $swooleResponse->write($chunk);
        }

        $swooleResponse->end();
    }
}
