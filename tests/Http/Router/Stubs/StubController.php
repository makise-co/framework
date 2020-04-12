<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Http\Router\Stubs;

use MakiseCo\Http\JsonResponse;

class StubController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(['hello' => 'world']);
    }

    public function wrongRoute1(): \stdClass
    {
        return new \stdClass;
    }

    public function wrongRoute2()
    {
        return null;
    }
}
