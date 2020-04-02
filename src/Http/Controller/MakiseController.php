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

class MakiseController
{
    public function index(): JsonResponse
    {
        return new JsonResponse(['message' => 'Kurisu loves you']);
    }
}
