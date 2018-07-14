<?php

namespace Everywhere\Api;

use alroniks\dtms\DateTime;
use Everywhere\Api\App\Container;
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\EventSourceInterface;
use Everywhere\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Everywhere\Api\Contract\Schema\BuilderInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerFactoryInterface;
use Everywhere\Api\Controllers\GraphqlController;
use Everywhere\Api\Controllers\SubscriptionController;
use Everywhere\Api\Integration\Events\NewMessageEvent;
use Everywhere\Api\Integration\Events\SubscriptionEvent;
use Everywhere\Api\Middleware\AuthenticationMiddleware;
use Everywhere\Api\Middleware\CorsMiddleware;
use Everywhere\Api\Middleware\ServerEvents\Stream;
use Everywhere\Api\Middleware\SubscriptionMiddleware;
use Everywhere\Api\Middleware\UploadMiddleware;
use Everywhere\Api\Subscription\SubscriptionManager;
use GraphQL\Deferred;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Executor;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\GraphQL;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

/**
 * @var $app App
 */
$app;

/**
 * @var $container Container
 */
$container = $app->getContainer();

$app->add($container[CorsMiddleware::class]);
$app->add($container[AuthenticationMiddleware::class]);

/**
 * Graphql query route
 */
$app->post("/graphql", GraphqlController::class . ":query")
    ->add($container[UploadMiddleware::class]);

$app->group('/subscriptions', function() use($app) {
    /**
     * Subscription register route
     */
    $app->post('/{streamId}', SubscriptionController::class . ":create");

    /**
     * Subscription unregister route
     */
    $app->delete('/{streamId}/{subscriptionId}', SubscriptionController::class . ":delete");

    /**
     * Subscription stream route
     */
    $app->get('/{streamId}', SubscriptionController::class . ":stream")
        ->add(SubscriptionMiddleware::class)
        ->setOutputBuffering(false);
});


/**
 * Subscription test route
 */
$app->get('/subscriptions-write/{userId}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($container) {

    /**
     * @var $eventManager EventManagerInterface
     */
    $eventManager = $container[EventManagerInterface::class];

    $eventManager->emit(new NewMessageEvent(17, 68, 354));

    return "\nDone!\n\n";
});
