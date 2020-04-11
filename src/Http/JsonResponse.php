<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http;

use function array_key_exists;
use function json_encode;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class JsonResponse extends Response
{
    public const JSON_OPTIONS = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;

    /**
     * JsonResponse constructor.
     * @param mixed $body
     * @param int $status
     * @param array $headers
     * @param int $options
     */
    public function __construct($body, int $status = 200, array $headers = [], int $options = self::JSON_OPTIONS)
    {
        $encoded = json_encode($body, $options);

        if (!array_key_exists('Content-Type', $headers)) {
            $headers['Content-Type'] = 'application/json';
        }

        parent::__construct($encoded, $status, $headers);
    }
}
