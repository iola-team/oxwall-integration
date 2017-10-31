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

$app->any("/graphql", $container->get(GraphQLMiddleware::class));
$app->any("/graphiql")->add(GraphiQLMiddleware::class);
