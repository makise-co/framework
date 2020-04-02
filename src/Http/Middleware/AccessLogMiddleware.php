<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function microtime;
use function strlen;
use function is_string;

class AccessLogMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \MakiseCo\Http\Request|ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = $request->server->get('REQUEST_TIME_FLOAT', microtime(true));
        $response = $handler->handle($request);

        $body = $response->getBody();
        $length = 0;

        if (is_string($body)) {
            $length = strlen($body);
        } elseif ($body instanceof StreamInterface) {
            $length = $body->getSize() ?? 0;
        }

        $this->logger->info('Request', [
            'duration' => $this->getTimeElapsed($start),
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'ip' => $request->getClientIp(),
                'ua' => $request->headers->get('User-Agent'),
            ],
            'response' => [
                'status' => $response->getStatusCode(),
                'length' => $length,
            ],
        ]);

        return $response;
    }

    protected function getTimeElapsed(float $time): float
    {
        return \round((microtime(true) - $time) * 1000, 6);
    }
}
