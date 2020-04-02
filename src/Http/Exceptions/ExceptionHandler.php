<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Exceptions;

use MakiseCo\Config\AppConfigInterface;
use MakiseCo\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function get_class;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class ExceptionHandler implements ExceptionHandlerInterface
{
    protected AppConfigInterface $config;
    protected LoggerInterface $logger;

    protected array $doNotLog = [
        HttpExceptionInterface::class,
    ];

    public function __construct(AppConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param Throwable $e
     * @param \MakiseCo\Http\Request|ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        if ($this->shouldReport($e)) {
            $this->log($request, $e);
        }

        return $this->render($request, $e);
    }

    protected function log(ServerRequestInterface $request, Throwable $e): void
    {
        $exceptionInfo = $this->getExceptionInfo($e);

        $message = $exceptionInfo['message'] ?? 'Error';
        unset($exceptionInfo['message']);

        $this->logger->error($message, $exceptionInfo);
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable $e
     * @return ResponseInterface
     */
    protected function render(ServerRequestInterface $request, Throwable $e): ResponseInterface
    {
        $statusCode = 500;
        $headers = [];

        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();
            $headers = $e->getHeaders();
        }

        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $statusCode,
            $headers,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    protected function shouldReport(Throwable $e): bool
    {
        foreach ($this->doNotLog as $ignoredException) {
            if ($e instanceof $ignoredException) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert the given exception to an array.
     *
     * @param Throwable $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e): array
    {
        if (!$this->config->isDebug()) {
            return [
                'message' => 'Server Error'
            ];
        }

        return $this->getExceptionInfo($e);
    }

    protected function getExceptionInfo(Throwable $e): array
    {
        return [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace(),
        ];
    }
}
