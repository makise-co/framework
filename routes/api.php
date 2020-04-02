<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

use MakiseCo\Http\Router\RouteCollector;
use MakiseCo\Http\JsonResponse;

/* @var RouteCollector $routes */

$routes->addGroup('', [
    'namespace' => 'MakiseCo\\Http\\Controller\\',
    'middleware' => [
        \MakiseCo\Http\Middleware\AccessLogMiddleware::class,
//        \MakiseCo\Http\Middleware\CorsMiddleware::class,
    ],
], function (RouteCollector $routes) {
    $routes->get('/', function () {
        return new JsonResponse(['message' => 'Hello, Okabe']);
    });

    $routes->get('/makise', 'MakiseController@index');

    $routes->addGroup('/okabe', [], function (RouteCollector $routes) {
        $routes->get('/say', 'OkabeController@say');

        $routes->get('/say/{phrase}', 'OkabeController@sayPhrase');

        $routes->get('/hello/{id:\d+}', 'OkabeController@helloId');
    });
});
