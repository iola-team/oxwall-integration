<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Schema\SubscriptionInterface;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerInterface;

class Subscription extends SyncPromise implements SubscriptionInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;
    protected $eventNames = [];
    protected $listener;

    public function __construct(array $eventNames, EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->eventNames = $eventNames;

        $this->listener = function($event) {
            $this->handleEvent($event);
        };

        $this->subscribe();
        $this->then(function() {
            $this->unsubscribe();
        });
    }

    /**
     * @param SubscriptionEventInterface $event
     *
     * @throws
     */
    protected function handleEvent($event)
    {
        if (!$event instanceof SubscriptionEventInterface) {
            return;
        }

        $this->resolve($event->getData());
    }

    protected function unsubscribe()
    {
        foreach ($this->eventNames as $eventName) {
            $this->eventManager->removeListener($eventName, $this->listener);
        }
    }

    protected function subscribe()
    {
        foreach ($this->eventNames as $eventName) {
            $this->eventManager->addListener($eventName, $this->listener);
        }
    }
}
