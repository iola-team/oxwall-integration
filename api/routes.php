<?php

namespace Everywhere\Api;

use Slim\App;
use Everywhere\Api\Middleware\GraphQLMiddleware;
use Slim\Views\PhpRenderer;

/**
 * @var $app App
 */
$app;

$container = $app->getContainer();

$app->any("/graphql", $container->get(GraphQLMiddleware::class));
$app->any("/graphiql", function ($request, $response, $args) {
    $phpView = new PhpRenderer("./View");
    return $phpView->render($response, "/index.php", $args);
});
