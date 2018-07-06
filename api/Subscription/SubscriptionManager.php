<?php

namespace Everywhere\Api\Subscription;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Schema\ContextInterface;
use Everywhere\Api\Contract\Subscription\SubscriptionManagerInterface;
use GraphQL\Deferred;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;

class SubscriptionManager implements SubscriptionManagerInterface
{
    protected $eventManager;
    protected $schema;
    protected $context;
    protected $promiseAdapter;

    protected $subscriptions;
    protected $resultQueue;

    public function __construct(
        EventManagerInterface $eventManager,
        Schema $schema,
        ContextInterface $context,
        PromiseAdapter $promiseAdapter
    )
    {
        $this->schema = $schema;
        $this->context = $context;
        $this->eventManager = $eventManager;
        $this->promiseAdapter = $promiseAdapter;

        $this->subscriptions = new \SplObjectStorage();
        $this->resultQueue = new \SplQueue();
        $this->resultQueue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
    }

    public function run()
    {
        Deferred::runQueue();
        SyncPromise::runQueue();
    }

    public function subscribe($query, $variables = [])
    {
        /**
         * @var $subscription SyncPromise
         */
        $subscription = GraphQL::promiseToExecute(
            $this->promiseAdapter,
            $this->schema,
            $query,
            null,
            $this->context,
            $variables
        )->adoptedPromise;

        $this->subscriptions->attach($subscription);

        /**
         * Process result and restart subscription
         */
        $subscription->then(function(ExecutionResult $result) use($subscription, $query, $variables) {
            $this->resultQueue->enqueue($result->data);
            $this->subscriptions->detach($subscription);

            $this->subscribe($query, $variables);
        });
    }

    public function getIterator()
    {
        return new \InfiniteIterator($this->resultQueue);
    }
}
