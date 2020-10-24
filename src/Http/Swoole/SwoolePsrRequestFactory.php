<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Swoole;

use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;

use function array_change_key_case;
use function Laminas\Diactoros\marshalMethodFromSapi;
use function Laminas\Diactoros\marshalProtocolVersionFromSapi;
use function Laminas\Diactoros\marshalUriFromSapi;
use function Laminas\Diactoros\normalizeUploadedFiles;

use const CASE_UPPER;

class SwoolePsrRequestFactory implements SwoolePsrRequestFactoryInterface
{
    public function create(SwooleRequest $request): ServerRequestInterface
    {
        // Aggregate values from Swoole request object
        $get = $request->get ?? [];
        $post = $request->post ?? [];
        $cookie = $request->cookie ?? [];
        $files = $request->files ?? [];
        $server = $request->server ?? [];
        $headers = $request->header ?? [];

        // Normalize SAPI params
        $server = array_change_key_case($server, CASE_UPPER);

        return new ServerRequest(
            $server,
            normalizeUploadedFiles($files),
            marshalUriFromSapi($server, $headers),
            marshalMethodFromSapi($server),
            new SwooleStream($request),
            $headers,
            $cookie,
            $get,
            $post,
            marshalProtocolVersionFromSapi($server)
        );
    }
}
