<?php

namespace Everywhere\Api;

use alroniks\dtms\DateTime;
use Everywhere\Api\App\Container;
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\EventSourceInterface;
use Everywhere\Api\Contract\Integration\SubscriptionEventsRepositoryInterface;
use Everywhere\Api\Contract\Schema\BuilderInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Integration\Events\SubscriptionEvent;
use Everywhere\Api\Middleware\AuthenticationMiddleware;
use Everywhere\Api\Middleware\CorsMiddleware;
use Everywhere\Api\Middleware\SSE\Stream;
use Everywhere\Api\Middleware\SubscriptionMiddleware;
use Everywhere\Api\Middleware\UploadMiddleware;
use GraphQL\Executor\Executor;
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

$app->post('/subscriptions', function() {
    return json_encode([
       "subId" => 1
    ]);
});

$app->get('/subscriptions/{id}', function(ServerRequestInterface $request, ResponseInterface $response) use ($container) {

    /**
     * @var $eventSource EventSourceInterface
     */
    $eventSource = $container[EventSourceInterface::class];

    /**
     * @var $subscriptionFactory SubscriptionFactoryInterface
     */
    $subscriptionFactory = $container[SubscriptionFactoryInterface::class];
    $subscription = $subscriptionFactory->create("messages.new");
    $iterator = $subscription->subscribe();

    $endTimeStamp = time() + 30;
    $fromTimeOffset = null;

    return $response->withBody(new Stream($iterator, function() use($eventSource, $endTimeStamp, &$fromTimeOffset) {
        /**
         * Stop streaming if last longer then given time
         */
        if ($endTimeStamp <= time()) {
            return false;
        }

        $fromTimeOffset = $eventSource->loadEvents($fromTimeOffset);

        usleep(500000); // Sleep for half a second
    }));
})->add(SubscriptionMiddleware::class)->setOutputBuffering(false);

$app->get('/subscriptions-write', function(ServerRequestInterface $request, ResponseInterface $response) use ($container) {

    /**
     * @var $eventManager EventManagerInterface
     */
    $eventManager = $container[EventManagerInterface::class];

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 1
    ]));

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 2
    ]));

    usleep(100000);

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 3
    ]));

    usleep(100000);

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 4
    ]));

    usleep(100000);

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 5
    ]));

    usleep(100000);

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 6
    ]));

    usleep(100000);

    $eventManager->emit(new SubscriptionEvent("messages.new", [
        "messageId" => 7
    ]));

    return "\nDone!\n\n";
});


$app->get('/subscriptions-test', function(ServerRequestInterface $request, ResponseInterface $response) use ($container) {

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
    $promise = GraphQL::promiseToExecute($promiseAdapter, $schema, $query, $rootValue, $context, $variableValues);

    $result = $promiseAdapter->wait($promise);

    return "\nDone!\n\n";
});
