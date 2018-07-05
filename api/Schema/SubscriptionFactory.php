<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use League\Event\ListenerAcceptorInterface;

class SubscriptionFactory implements SubscriptionFactoryInterface
{
    /**
     * @var ListenerAcceptorInterface
     */
    protected $eventSource;

    public function __construct(ListenerAcceptorInterface $eventSource)
    {
        $this->eventSource = $eventSource;
    }

    public function create($eventNames, callable $filter = null)
    {
        return new Subscription(
            is_string($eventNames) ? [$eventNames]: $eventNames,
            $this->eventSource
        );
    }
}
