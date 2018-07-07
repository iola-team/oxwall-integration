<?php

namespace Everywhere\Api\Subscription;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerInterface;
use GraphQL\Deferred;
use GraphQL\Error\InvariantViolation;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Overblog\DataLoader\DataLoader;

class SubscriptionManager implements SubscriptionManagerInterface
{
    protected $eventManager;
    protected $schema;
    protected $context;
    protected $promiseAdapter;

    protected $promises;
    protected $resultQueue;

    public function __construct(
        EventManagerInterface $eventManager,
        Schema $schema,
        ContextInterface $context,
        SyncPromiseAdapter $promiseAdapter
    )
    {
        $this->schema = $schema;
        $this->context = $context;
        $this->eventManager = $eventManager;
        $this->promiseAdapter = $promiseAdapter;

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

    public function subscribe($query, $variables = [])
    {
        $subscriptionPromise = GraphQL::promiseToExecute(
            $this->promiseAdapter,
            $this->schema,
            $query,
            null,
            $this->context,
            $variables
        )->adoptedPromise;

        $promise = new Promise($subscriptionPromise, $this->promiseAdapter);
        $this->promises->attach($promise);

        /**
         * Process result and restart subscription
         */
        $promise->then(function(ExecutionResult $result) use($promise, $query, $variables) {
            $this->resultQueue->enqueue($result->data);
            $this->promises->detach($promise);

            $this->subscribe($query, $variables);
        });
    }

    public function getIterator()
    {
        return new \InfiniteIterator($this->resultQueue);
    }
}
