<?php

namespace Everywhere\Api;

use Everywhere\Api\Middleware\AuthMiddleware;
use Everywhere\Api\Middleware\AuthenticationMiddleware;
use Everywhere\Api\Middleware\CorsMiddleware;
use Everywhere\Api\Middleware\UploadMiddleware;
use Slim\App;
use Everywhere\Api\Middleware\GraphQLMiddleware;

/**
 * @var $app App
 */
$app;

$container = $app->getContainer();

$app->add($container[CorsMiddleware::class]);
$app->add($container[AuthenticationMiddleware::class]);
$app->any("/graphql", $container[GraphQLMiddleware::class])
    ->add($container[UploadMiddleware::class]);
