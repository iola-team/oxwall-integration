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
use Everywhere\Api\Controllers\SubscriptionController;
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
use Everywhere\Api\Middleware\GraphQLMiddleware;

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

$app->any("/graphql", $container[GraphQLMiddleware::class])
    ->add($container[UploadMiddleware::class]);

$app->post('/subscriptions', SubscriptionController::class . ":create");
$app->delete('/subscriptions/{id}', SubscriptionController::class . ":delete");
$app->get('/subscriptions/{id}', SubscriptionController::class . ":stream")
    ->add(SubscriptionMiddleware::class)
    ->setOutputBuffering(false);

$app->get('/subscriptions-write/{userId}', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($container) {

    /**
     * @var $eventManager EventManagerInterface
     */
    $eventManager = $container[EventManagerInterface::class];

    $eventManager->emit(new SubscriptionEvent("messages.new", $args["userId"]));


    return "\nDone!\n\n";
});


$app->get('/subscriptions-test', function(ServerRequestInterface $request, ResponseInterface $response) use ($container) {

    /**
     * Clear output buffer
     */
    while (ob_get_level() > 0) {
        ob_end_flush();
    }

    /**
     * Disable output buffer
     */
    ob_implicit_flush();


    /**
     * @var $eventSource EventSourceInterface
     */
    $eventSource = $container[EventSourceInterface::class];

    /**
     * @var SyncPromiseAdapter $promiseAdapter
     */
    $promiseAdapter = $container[PromiseAdapter::class];

    /**
     * @var BuilderInterface $schemaBuilder
     */
    $schemaBuilder = $container[BuilderInterface::class];
    $context = $container[ContextInterface::class];
    $schema = $schemaBuilder->build();

    $rootValue = null;
    $variableValues = [];
    $query = "subscription { onMessageAdd }";

//    $result = $promiseAdapter->wait($promise);

    $endTimeStamp = time() + 30;
    $fromTimeOffset = null;

    while (true) {
        /**
         * @var $promise SyncPromise
         */
        $promise = GraphQL::promiseToExecute($promiseAdapter, $schema, $query, $rootValue, $context, $variableValues)->adoptedPromise;

        while ($promise->state === SyncPromise::PENDING) {
            /**
             * Stop streaming if last longer then given time
             */
            if ($endTimeStamp <= time()) {
                break 2;
            }

            $fromTimeOffset = $eventSource->loadEvents($fromTimeOffset);

            Deferred::runQueue();
            SyncPromise::runQueue();

            usleep(500000);
        }

        /**
         * @var $result ExecutionResult
         */
        $result = $promise->result;

        print_r($result->data);
    }

    return "\nDone!\n\n";
});
