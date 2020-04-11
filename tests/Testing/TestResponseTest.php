<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Tests\Testing;

use MakiseCo\Http\JsonResponse;
use MakiseCo\Http\Response;
use MakiseCo\Testing\Http\TestResponse;
use PHPUnit\Framework\TestCase;

class TestResponseTest extends TestCase
{
    protected const COMPLICATED_DATA = [
        'data' => [
            [
                'id' => 1,
                'name' => 2,
                'object' => [
                    'some' => 3,
                ]
            ]
        ]
    ];

    public function testAssertStatusCode(): void
    {
        $makiseResponse = new Response('Hello world', 200);
        $response = new TestResponse($makiseResponse);

        $response->assertStatus(200);
    }

    public function testAssertJsonStructure(): void
    {
        $complicatedStructure = [
            'data' => [
                [
                    'id',
                    'name',
                    'object' => [
                        'some'
                    ]
                ]
            ]
        ];

        $makiseResponse = new JsonResponse(self::COMPLICATED_DATA, 200);
        $response = new TestResponse($makiseResponse);

        $response->assertJsonStructure($complicatedStructure);
    }

    public function testAssertJson(): void
    {
        $makiseResponse = new JsonResponse(self::COMPLICATED_DATA, 200);
        $response = new TestResponse($makiseResponse);

        $response->assertJson(self::COMPLICATED_DATA);
    }

    public function testAssertJsonFragment(): void
    {
        $makiseResponse = new JsonResponse(self::COMPLICATED_DATA, 200);
        $response = new TestResponse($makiseResponse);

        $response->assertJsonFragment(['some' => 3]);
    }

    public function testAssertJsonCount(): void
    {
        $makiseResponse = new JsonResponse(self::COMPLICATED_DATA, 200);
        $response = new TestResponse($makiseResponse);

        $response->assertJsonCount(1, 'data');
    }

    public function testAssertSee(): void
    {
        $makiseResponse = new Response('<p>Some value</p><br>Bla');
        $response = new TestResponse($makiseResponse);

        $response->assertSee('Bla');
    }
}
