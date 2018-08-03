<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use GraphQL\Executor\Promise\PromiseAdapter;

class SubscriptionFactory implements SubscriptionFactoryInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var PromiseAdapter
     */
    protected $promiseAdapter;

    public function __construct(EventManagerInterface $eventManager, PromiseAdapter $promiseAdapter)
    {
        $this->eventManager = $eventManager;
        $this->promiseAdapter = $promiseAdapter;
    }

    public function create($eventNames, callable $filter = null, callable $resolve = null)
    {


        return new Subscription(
            is_string($eventNames) ? [$eventNames]: $eventNames,
            $filter,
            $resolve,
            $this->eventManager,
            $this->promiseAdapter
        );
    }
}
