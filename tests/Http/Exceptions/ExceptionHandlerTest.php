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
use Laminas\Diactoros\ServerRequest;
use MakiseCo\Config\ConfigRepositoryInterface;
use MakiseCo\Config\Repository;
use MakiseCo\Http\Exceptions\JsonExceptionHandler;
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
        return new ServerRequest(
            [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $uri
            ],
            [],
            $uri,
            $method
        );
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
            ->expects(self::once())
            ->method('error')
            ->with(
                $message,
                self::callback(static function (array $args) use ($method, $uri) {
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

        $handler = new JsonExceptionHandler($config, $logger);

        $request = $this->getFakeRequest($method, $uri);

        $exception = new InvalidArgumentException($message);

        $handler->handle($exception, $request);
    }
}
