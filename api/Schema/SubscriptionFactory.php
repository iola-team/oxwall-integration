<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;

class SubscriptionFactory implements SubscriptionFactoryInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    public function __construct(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function create($eventNames, callable $filter = null, callable $resolve = null)
    {


        return new Subscription(
            is_string($eventNames) ? [$eventNames]: $eventNames,
            $filter,
            $resolve,
            $this->eventManager
        );
    }
}
