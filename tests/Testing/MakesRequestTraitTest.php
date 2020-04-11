<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Testing;

use DI\Container;
use MakiseCo\Http\Handler\RequestHandler;
use MakiseCo\Http\Request;
use MakiseCo\Testing\Concerns\MakesHttpRequests;
use PHPUnit\Framework\TestCase;

class MakesRequestTraitTest extends TestCase
{
    use MakesHttpRequests;

    protected Container $container;
    /**
     * @var RequestHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected RequestHandler $requestHandlerMock;

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->requestHandlerMock = $this->createMock(RequestHandler::class);
        $this->container->set(RequestHandler::class, $this->requestHandlerMock);
    }

    public function testGet(): void
    {
        $this
            ->requestHandlerMock
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (Request $request) {
                return '/some' === $request->getRequestUri() && 'GET' === $request->getMethod();
            }));

        $this->get('/some');
    }

    public function testPost(): void
    {
        $this
            ->requestHandlerMock
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (Request $request) {
                return '/some' === $request->getRequestUri()
                    && 'POST' === $request->getMethod()
                    && 1 === $request->request->get('some');
            }));

        $this->post('/some', ['some' => 1]);
    }

    public function testJson(): void
    {
        $this
            ->requestHandlerMock
            ->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (Request $request) {
                return '/some' === $request->getRequestUri()
                    && 'POST' === $request->getMethod()
                    && \json_encode(['some' => 1]) === $request->getBody()->__toString()
                    && ['some' => 1] === $request->request->all();
            }));

        $this->postJson('/some', ['some' => 1]);
    }
}
