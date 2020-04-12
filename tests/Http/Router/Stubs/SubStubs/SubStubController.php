<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Http\Router\Stubs\SubStubs;

use MakiseCo\Http\JsonResponse;

class SubStubController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(['hello' => 'world']);
    }
}
