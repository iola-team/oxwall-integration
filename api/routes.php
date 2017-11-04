<?php

namespace Everywhere\Api;

use Slim\App;
use Everywhere\Api\Middleware\GraphQLMiddleware;
use GraphiQLMiddleware\GraphiQLMiddleware;

/**
 * @var $app App
 */
$app;

$container = $app->getContainer();

$app->get("/graphql", function($request, $response, $args) {
    return $response;
})->add(new GraphiQLMiddleware(['ingoreRoute' => true]));
$app->post("/graphql", $container->get(GraphQLMiddleware::class));
