<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Http\Controller;

use MakiseCo\Http\JsonResponse;
use MakiseCo\Http\Request;

class OkabeController
{
    public function say(): JsonResponse
    {
        return new JsonResponse(['message' => 'Hello from Okabe']);
    }

    public function sayPhrase(string $phrase, Request $request2): JsonResponse
    {
        return new JsonResponse(['message' => "Okabe: {$phrase}"]);
    }

    public function helloId(int $id): JsonResponse
    {
        return new JsonResponse(['message' => "Hello ID: {$id}"]);
    }
}
