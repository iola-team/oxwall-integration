<?php

namespace Iola\Api;

use Slim\App;
use Iola\Api\App\Container;
use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Controllers\GraphqlController;
use Iola\Api\Controllers\SubscriptionController;
use Iola\Api\Integration\Events\MessageUpdatedEvent;
use Iola\Api\Middleware\AuthenticationMiddleware;
use Iola\Api\Middleware\CorsMiddleware;
use Iola\Api\Middleware\SubscriptionMiddleware;
use Iola\Api\Middleware\UploadMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Iola\Api\Middleware\SessionMiddleware;
use Iola\Api\Middleware\RequestTrackingMiddleware;

/**
 * @var $app App
 */
$app;

/**
 * @var $container Container
 */
$container = $app->getContainer();

/**
 * Graphql query route
 */
$app->post("/graphql", GraphqlController::class . ":query")
    ->add($container[UploadMiddleware::class]);

$app->group("/subscriptions", function() use($app) {
    /**
     * Subscription register route
     */
    $app->post("/{streamId}", SubscriptionController::class . ":create");

    /**
     * Subscription unregister route
     */
    $app->delete("/{streamId}/{subscriptionId}", SubscriptionController::class . ":delete");

    /**
     * Subscription stream route
     */
    $app->get("/{streamId}", SubscriptionController::class . ":stream")
        ->add(SubscriptionMiddleware::class)
        ->setOutputBuffering(false);
});


/**
 * Subscription test route
 */
$app->get("/subscriptions-write/{messageId}", function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($container) {

    /**
     * @var $eventManager EventManagerInterface
     */
    $eventManager = $container[EventManagerInterface::class];

    $eventManager->emit(new MessageUpdatedEvent($args["messageId"]));

    return "\nDone!\n\n";
});

/**
 * Health Check
 * (To check if the integration plugin was correctly installed)
 */
$app->get("/health", function ($request, $response, $args) {
    return json_encode(["success" => "All good in the hood"]);
});


/**
 * Middleware
 */

$app->add($container[SessionMiddleware::class]);
$app->add($container[RequestTrackingMiddleware::class]);
$app->add($container[AuthenticationMiddleware::class]);
$app->add($container[CorsMiddleware::class]);