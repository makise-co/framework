<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Swoole;

use MakiseCo\Http\FakeStream;
use MakiseCo\Http\Request;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

use function array_key_exists;
use function in_array;
use function json_decode;
use function parse_str;
use function str_replace;
use function strpos;
use function strtoupper;

/**
 * @copyright https://github.com/swooletw/laravel-swoole/
 */
class RequestFactory
{
    public function createFromSwoole(SwooleRequest $swooleRequest): Request
    {
        $get = $swooleRequest->get ?? [];
        $post = $swooleRequest->post ?? [];
        $cookie = $swooleRequest->cookie ?? [];
        $files = $swooleRequest->files ?? [];
        $server = $swooleRequest->server ?? [];
        $headers = $swooleRequest->header ?? [];
        $content = $swooleRequest->rawContent();

        /*
         |--------------------------------------------------------------------------
         | Copy from \Symfony\Component\HttpFoundation\Request::createFromGlobals().
         |--------------------------------------------------------------------------
         |
         | With the php's bug #66606, the php's built-in web server
         | stores the Content-Type and Content-Length header values in
         | HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH fields.
         |
         */
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $server)) {
                $server['CONTENT_LENGTH'] = $server['HTTP_CONTENT_LENGTH'];
            }

            if (array_key_exists('HTTP_CONTENT_TYPE', $server)) {
                $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
            }
        }

        $makiseRequest = new Request(
            $get,
            $post,
            [],
            $cookie,
            $files,
            $this->transformServer($server, $headers),
            new FakeStream($content),
        );

        $this->decodeContent($makiseRequest, $content);

        return $makiseRequest;
    }

    protected function decodeContent(Request $request, string $content): void
    {
        if ('' === $content) {
            return;
        }

        $contentType = $request->headers->get('CONTENT_TYPE', '');
        $requestMethod = $request->server->get('REQUEST_METHOD', 'GET');

        if (0 === strpos($contentType, 'application/x-www-form-urlencoded')
            && in_array($requestMethod, ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($content, $data);
            $request->request = new ParameterBag($data);
        }

        if (0 === strpos($contentType, 'application/json')
            && in_array($requestMethod, ['POST', 'PUT', 'DELETE', 'PATCH'])
        ) {
            // TODO: Benchmark it versus try/catch
            $data = json_decode($content, true);
            if (false !== $data) {
                $request->request = new ParameterBag((array)$data);
            }
        }
    }

    protected function transformServer(array $server, array $headers): array
    {
        $__SERVER = [];

        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $__SERVER[$key] = $value;
        }

        foreach ($headers as $key => $value) {
            $key = str_replace('-', '_', $key);
            $key = strtoupper($key);

            if (!in_array($key, ['REMOTE_ADDR', 'SERVER_PORT', 'HTTPS'])) {
                $key = 'HTTP_' . $key;
            }

            $__SERVER[$key] = $value;
        }

        return $__SERVER;
    }
}
