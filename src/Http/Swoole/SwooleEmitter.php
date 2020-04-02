<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Swoole;

use MakiseCo\Http\Response as MakiseResponse;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Response as SwooleResponse;

use function implode;
use function str_split;
use function time;

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
     * @param MakiseResponse $makiseResponse
     */
    public function emit(SwooleResponse $swooleResponse, MakiseResponse $makiseResponse): void
    {
        $this->emitStatusCode($swooleResponse, $makiseResponse);
        $this->emitHeaders($swooleResponse, $makiseResponse);
        $this->emitCookies($swooleResponse, $makiseResponse);
        $this->emitBody($swooleResponse, $makiseResponse);
    }

    /**
     * Emit the status code
     *
     * @param SwooleResponse $swooleResponse
     * @param MakiseResponse $makiseResponse
     */
    private function emitStatusCode(SwooleResponse $swooleResponse, MakiseResponse $makiseResponse): void
    {
        $swooleResponse->status($makiseResponse->getStatusCode());
    }

    /**
     * Emit the headers
     *
     * @param SwooleResponse $swooleResponse
     * @param MakiseResponse $makiseResponse
     */
    private function emitHeaders(SwooleResponse $swooleResponse, MakiseResponse $makiseResponse): void
    {
        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!$makiseResponse->hasHeader('Date')) {
            $makiseResponse->setDate(\DateTime::createFromFormat('U', time()));
        }

        foreach ($makiseResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $swooleResponse->header($name, implode(', ', $values));
        }
    }

    /**
     * Emit the message body.
     *
     * @param SwooleResponse $swooleResponse
     * @param MakiseResponse $makiseResponse
     */
    private function emitBody(SwooleResponse $swooleResponse, MakiseResponse $makiseResponse): void
    {
        $body = $makiseResponse->getBody();

        if ($body instanceof StreamInterface) {
            $this->emitStreamedBody($swooleResponse, $body);

            return;
        }

        $this->emitRawBody($swooleResponse, $body);
    }

    private function emitRawBody(SwooleResponse $swooleResponse, string $body): void
    {
        foreach (str_split($body, self::CHUNK_SIZE) as $chunk) {
            $swooleResponse->write($chunk);
        }

        $swooleResponse->end();
    }

    private function emitStreamedBody(SwooleResponse $swooleResponse, StreamInterface $body): void
    {
        $body->rewind();

        if ($body->getSize() <= static::CHUNK_SIZE) {
            $swooleResponse->write($body->getContents());
            $swooleResponse->end();

            return;
        }

        while (!$body->eof()) {
            $swooleResponse->write($body->read(static::CHUNK_SIZE));
        }

        $swooleResponse->end();
    }

    /**
     * Emit the cookies
     *
     * @param SwooleResponse $swooleResponse
     * @param MakiseResponse $makiseResponse
     */
    private function emitCookies(SwooleResponse $swooleResponse, MakiseResponse $makiseResponse): void
    {
        foreach ($makiseResponse->headers->getCookies() as $cookie) {
            if ($cookie->isRaw()) {
                $swooleResponse->rawcookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly(),
                    $cookie->getSameSite()
                );
            } else {
                $swooleResponse->cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getExpiresTime(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttpOnly(),
                    $cookie->getSameSite()
                );
            }
        }
    }
}
