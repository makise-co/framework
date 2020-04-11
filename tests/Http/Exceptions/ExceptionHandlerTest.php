<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Http\Exceptions;

use InvalidArgumentException;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Config\Repository;
use MakiseCo\Http\Exceptions\ExceptionHandler;
use MakiseCo\Http\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function array_key_exists;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @return LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFakeLogger(): LoggerInterface
    {
        return $this->createMock(NullLogger::class);
    }

    /**
     * @return ConfigRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFakeConfig(): ConfigRepositoryInterface
    {
        return $this->createMock(Repository::class);
    }

    protected function getFakeRequest(string $method, string $uri): ServerRequestInterface
    {
        $request = new Request();
        $request->server->set('REQUEST_METHOD', $method);
        $request->server->set('REQUEST_URI', $uri);

        return $request;
    }

    /**
     * @testdox Check that the ExceptionHandler is logging request method and request URI
     */
    public function testLoggingRequestInfo(): void
    {
        $config = $this->getFakeConfig();
        $config
            ->method('get')
            ->with('app.debug')
            ->willReturn(true);

        $method = 'GET';
        $uri = '/makise?some=1';
        $message = 'Something went wrong';

        $logger = $this->getFakeLogger();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $message,
                $this->callback(static function (array $args) use ($method, $uri) {
                    if (!array_key_exists('extra', $args)) {
                        return false;
                    }

                    $extra = $args['extra'];

                    if (!array_key_exists('uri', $extra) || $uri !== $extra['uri']) {
                        return false;
                    }

                    if (!array_key_exists('method', $extra) || $method !== $extra['method']) {
                        return false;
                    }

                    return true;
                })
            );

        $handler = new ExceptionHandler($config, $logger);

        $request = $this->getFakeRequest($method, $uri);

        $exception = new InvalidArgumentException($message);

        $handler->handle($exception, $request);
    }
}
