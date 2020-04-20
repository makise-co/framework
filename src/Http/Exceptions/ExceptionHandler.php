<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Exceptions;

use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Http\JsonResponse;
use MakiseCo\Http\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function get_class;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class ExceptionHandler implements ExceptionHandlerInterface
{
    protected ConfigRepositoryInterface $config;
    protected LoggerInterface $logger;

    protected array $doNotLog = [
        HttpExceptionInterface::class,
    ];

    public function __construct(ConfigRepositoryInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

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

        $extra = [
            'uri' => $request->getUri()->__toString(),
            'method' => $request->getMethod(),
        ];

        $userId = $this->getUserIdFromRequest($request);
        if (null !== $userId) {
            $extra['userId'] = $userId;
        }

        $exceptionInfo['extra'] = $extra;

        $this->logger->error($message, $exceptionInfo);
    }

    protected function render(ServerRequestInterface $request, Throwable $e): ResponseInterface
    {
        if ($e instanceof HttpExceptionInterface) {
            return $this->renderHttpException($request, $e);
        }

        return $this->renderThrowable($request, $e);
    }


    protected function renderThrowable(ServerRequestInterface $request, Throwable $e): JsonResponse
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            500,
            [],
            $this->getJsonOptions()
        );
    }

    protected function renderHttpException(ServerRequestInterface $request, HttpExceptionInterface $e): JsonResponse
    {
        $statusCode = $e->getStatusCode();
        $headers = $e->getHeaders();

        return new JsonResponse(
            ['message' => $e->getMessage()],
            $statusCode,
            $headers,
            $this->getJsonOptions()
        );
    }

    protected function getJsonOptions(): int
    {
        return $this->config->get('app.debug') ?
            JsonResponse::JSON_OPTIONS | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES :
            JsonResponse::JSON_OPTIONS;
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
        if (!$this->config->get('app.debug')) {
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

    /**
     * @param ServerRequestInterface $request
     * @return string|int|null
     */
    protected function getUserIdFromRequest(ServerRequestInterface $request)
    {
        if (!$request instanceof Request) {
            return null;
        }

        $context = $request->getContext();
        if (null === $context) {
            return null;
        }

        $authContext = $context->getAuthContext();
        if (null === $authContext) {
            return null;
        }

        $user = $authContext->getUser();
        if (null === $user) {
            return null;
        }

        return $user->getIdentifier();
    }
}
