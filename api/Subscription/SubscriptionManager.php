<?php

namespace Everywhere\Api\Subscription;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerInterface;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Server\ServerConfig;

class SubscriptionManager implements SubscriptionManagerInterface
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var ServerConfig
     */
    protected $serverConfig;

    protected $promises;
    protected $resultQueue;

    public function __construct(EventManagerInterface $eventManager, ServerConfig $serverConfig)
    {
        $this->eventManager = $eventManager;
        $this->serverConfig = $serverConfig;
        $this->promiseAdapter = $serverConfig->getPromiseAdapter();

        $this->promises = new \SplObjectStorage();
        $this->resultQueue = new \SplQueue();
        $this->resultQueue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);

        /**
         * Auto run on every subscription event event
         */
        $this->eventManager->addListener("*", function($event) {
            if ($event instanceof SubscriptionEventInterface) {
                $this->run();
            }
        }, EventManagerInterface::P_LOW);
    }

    protected function run()
    {
        foreach ($this->promises as $promise) {
            try {
                $this->promiseAdapter->wait($promise);
            } catch (InvariantViolation $exception) {
                // Do nothing...
            }
        }
    }

    public function subscribe($query, $variables, $subscriptionKey = null)
    {
        $subscriptionPromise = GraphQL::promiseToExecute(
            $this->promiseAdapter,
            $this->serverConfig->getSchema(),
            $query,
            null,
            $this->serverConfig->getContext(),
            $variables,
            null,
            $this->serverConfig->getFieldResolver(),
            $this->serverConfig->getValidationRules()
        )->adoptedPromise;

        $promise = new Promise($subscriptionPromise, $this->promiseAdapter);
        $this->promises->attach($promise);

        /**
         * Process result and restart subscription
         */
        $promise->then(function(ExecutionResult $result) use($promise, $subscriptionKey, $query, $variables) {
            $key = $subscriptionKey ? $subscriptionKey : uniqid();

            $this->resultQueue->enqueue([
                "data" => $result->data,
                "key" => $key
            ]);
            $this->promises->detach($promise);
            $this->subscribe($query, $variables, $key);
        });
    }

    public function getIterator()
    {
        return new \InfiniteIterator($this->resultQueue);
    }
}
