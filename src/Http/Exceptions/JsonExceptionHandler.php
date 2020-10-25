<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Exceptions;

use Laminas\Diactoros\Response;
use MakiseCo\Http\Router\Exception\MethodNotAllowedException;
use MakiseCo\Http\Router\Exception\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_LINE_TERMINATORS;

class JsonExceptionHandler extends ExceptionHandler
{
    protected function renderHttpException(
        ServerRequestInterface $request,
        HttpExceptionInterface $e
    ): ResponseInterface {
        $statusCode = $e->getStatusCode();
        $headers = $e->getHeaders();

        return new Response\JsonResponse(
            ['message' => $e->getMessage()],
            $statusCode,
            $headers,
            $this->getJsonOptions()
        );
    }

    protected function renderThrowable(ServerRequestInterface $request, Throwable $e): ResponseInterface
    {
        return new Response\JsonResponse(
            $this->convertExceptionToArray($e),
            500,
            [],
            $this->getJsonOptions()
        );
    }

    protected function renderRouteNotFound(
        ServerRequestInterface $request,
        RouteNotFoundException $e
    ): ResponseInterface {
        return new Response\JsonResponse(
            ['message' => 'Not Found'],
            404,
            [],
            $this->getJsonOptions()
        );
    }

    protected function renderMethodNotAllowed(
        ServerRequestInterface $request,
        MethodNotAllowedException $e
    ): ResponseInterface {
        return new Response\JsonResponse(
            ['message' => 'Method Not Allowed'],
            405,
            ['Allow' => $e->getAllowedMethods()],
            $this->getJsonOptions()
        );
    }

    protected function getJsonOptions(): int
    {
        $defaultOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS;

        return $this->config->get('app.debug') ?
            $defaultOptions | JSON_PRETTY_PRINT :
            $defaultOptions;
    }
}
