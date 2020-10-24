<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Exceptions;

use MakiseCo\Auth\AuthenticatableInterface;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Http\Router\Exception\MethodNotAllowedException;
use MakiseCo\Http\Router\Exception\RouteNotFoundException;
use MakiseCo\Middleware\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function get_class;

abstract class ExceptionHandler implements ErrorHandlerInterface
{
    protected ConfigRepositoryInterface $config;
    protected LoggerInterface $logger;

    protected array $doNotLog = [
        HttpExceptionInterface::class,
        RouteNotFoundException::class,
        MethodNotAllowedException::class,
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

        if ($e instanceof RouteNotFoundException) {
            return $this->renderRouteNotFound($request, $e);
        }

        if ($e instanceof MethodNotAllowedException) {
            return $this->renderMethodNotAllowed($request, $e);
        }

        return $this->renderThrowable($request, $e);
    }

    abstract protected function renderThrowable(ServerRequestInterface $request, Throwable $e): ResponseInterface;

    abstract protected function renderHttpException(
        ServerRequestInterface $request,
        HttpExceptionInterface $e
    ): ResponseInterface;

    abstract protected function renderRouteNotFound(
        ServerRequestInterface $request,
        RouteNotFoundException $e
    ): ResponseInterface;

    abstract protected function renderMethodNotAllowed(
        ServerRequestInterface $request,
        MethodNotAllowedException $e
    ): ResponseInterface;

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
        $user = $request->getAttribute(AuthenticatableInterface::class, null);
        if ($user instanceof AuthenticatableInterface) {
            return $user->getAuthIdentifier();
        }

        return null;
    }
}
