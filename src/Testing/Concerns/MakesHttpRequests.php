<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 *
 */

declare(strict_types=1);

namespace MakiseCo\Testing\Concerns;

use MakiseCo\Http\FakeStream;
use MakiseCo\Http\Handler\RequestHandler;
use MakiseCo\Http\Request;
use MakiseCo\Testing\Http\TestResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

trait MakesHttpRequests
{
    /**
     * Additional headers for the request.
     *
     * @var array
     */
    protected array $defaultHeaders = [];

    /**
     * Additional cookies for the request.
     *
     * @var array
     */
    protected array $defaultCookies = [];

    /**
     * Additional server variables for the request.
     *
     * @var array
     */
    protected array $serverVariables = [];

    /**
     * Define additional headers to be sent with the request.
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);

        return $this;
    }

    /**
     * Add a header to be sent with the request.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function withHeader(string $name, string $value): self
    {
        $this->defaultHeaders[$name] = $value;

        return $this;
    }

    /**
     * Flush all the configured headers.
     *
     * @return $this
     */
    public function flushHeaders(): self
    {
        $this->defaultHeaders = [];

        return $this;
    }

    /**
     * Define a set of server variables to be sent with the requests.
     *
     * @param array $server
     * @return $this
     */
    public function withServerVariables(array $server): self
    {
        $this->serverVariables = $server;

        return $this;
    }

    /**
     * Define additional cookies to be sent with the request.
     *
     * @param array $cookies
     * @return $this
     */
    public function withCookies(array $cookies): self
    {
        $this->defaultCookies = array_merge($this->defaultCookies, $cookies);

        return $this;
    }

    /**
     * Add a cookie to be sent with the request.
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function withCookie(string $name, string $value): self
    {
        $this->defaultCookies[$name] = $value;

        return $this;
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param string $uri
     * @param array $headers
     * @return TestResponse
     */
    public function get($uri, array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('GET', $uri, [], $this->defaultCookies, [], $server);
    }

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param string $uri
     * @param array $headers
     * @return TestResponse
     */
    public function getJson($uri, array $headers = []): TestResponse
    {
        return $this->json('GET', $uri, [], $headers);
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function post($uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('POST', $uri, $data, $this->defaultCookies, [], $server);
    }

    /**
     * Visit the given URI with a POST request, expecting a JSON response.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function postJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function put($uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('PUT', $uri, $data, $this->defaultCookies, [], $server);
    }

    /**
     * Visit the given URI with a PUT request, expecting a JSON response.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function putJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function patch($uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('PATCH', $uri, $data, $this->defaultCookies, [], $server);
    }

    /**
     * Visit the given URI with a PATCH request, expecting a JSON response.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function patchJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function delete($uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('DELETE', $uri, $data, $this->defaultCookies, [], $server);
    }

    /**
     * Visit the given URI with a DELETE request, expecting a JSON response.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function deleteJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a OPTIONS request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function options($uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('OPTIONS', $uri, $data, $this->defaultCookies, [], $server);
    }

    /**
     * Visit the given URI with a OPTIONS request, expecting a JSON response.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function optionsJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('OPTIONS', $uri, $data, $headers);
    }

    /**
     * Call the given URI with a JSON request.
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return TestResponse
     */
    public function json($method, $uri, array $data = [], array $headers = []): TestResponse
    {
        $files = $this->extractFilesFromDataArray($data);

        $content = new FakeStream(json_encode($data));

        $headers = array_merge([
            'CONTENT_LENGTH' => $content->getSize(),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);

        return $this->call(
            $method, $uri, $data, [], $files, $this->transformHeadersToServerVars($headers), $content
        );
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param string $method
     * @param string $uri
     * @param array $parameters
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param string|null|FakeStream $content
     * @return TestResponse
     */
    public function call(
        $method,
        $uri,
        $parameters = [],
        $cookies = [],
        $files = [],
        $server = [],
        $content = null
    ): TestResponse {
        $server['REQUEST_METHOD'] = $method;
        $server['REQUEST_URI'] = rawurldecode(parse_url($uri, PHP_URL_PATH));

        /* @var RequestHandler $kernel */
        $kernel = $this->container->get(RequestHandler::class);

        $files = array_merge($files, $this->extractFilesFromDataArray($parameters));

        parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $query);

        $request = new Request(
            $query,
            $parameters,
            [],
            $cookies,
            $files,
            array_replace($this->serverVariables, $server),
            $content
        );

        $response = $kernel->handle($request);

        return $this->createTestResponse($response);
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param array $headers
     * @return string[]
     */
    protected function transformHeadersToServerVars(array $headers): array
    {
        $result = [];

        foreach ($headers as $key => $header) {
            $result[$this->formatServerHeaderKey($key)] = $header;
        }

        return $result;
    }

    /**
     * Format the header name for the server array.
     *
     * @param string $name
     * @return string
     */
    protected function formatServerHeaderKey(string $name): string
    {
        if (false === strpos($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_' . $name;
        }

        return $name;
    }

    /**
     * Extract the file uploads from the given data array.
     *
     * @param array $data
     * @return array
     */
    protected function extractFilesFromDataArray(&$data): array
    {
        $files = [];

        foreach ($data as $key => $value) {
            if ($value instanceof SymfonyUploadedFile) {
                $files[$key] = $value;

                unset($data[$key]);
            }

            if (is_array($value)) {
                $files[$key] = $this->extractFilesFromDataArray($value);

                $data[$key] = $value;
            }
        }

        return $files;
    }

    /**
     * Create the test response instance from the given response.
     * @param ResponseInterface $response
     * @return TestResponse
     */
    protected function createTestResponse(ResponseInterface $response): TestResponse
    {
        return new TestResponse($response);
    }
}
