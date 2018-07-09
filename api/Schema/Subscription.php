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

    /**
     * @var string[]
     */
    protected $eventNames = [];

    /**
     * @var callable
     */
    protected $filter;

    /**
     * @var callable
     */
    protected $resolve;

    /**
     * @var \Closure
     */
    protected $listener;

    public function __construct(
        array $eventNames,
        callable $filter = null,
        callable $resolve = null,
        EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
        $this->eventNames = $eventNames;
        $this->filter = $filter;
        $this->resolve = $resolve;

        $this->listener = function($event) {
            $this->handleEvent($event);
        };

        $this->subscribe();
        $this->then(function() {
            $this->unsubscribe();
        });
    }

    protected function resolveValue($data)
    {
        return is_callable($this->resolve)
            ? call_user_func($this->resolve, $data)
            : $data;
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

        if (is_callable($this->filter) && call_user_func($this->filter, $event->getData()) === false) {
            return;
        }

        $data = $this->resolveValue($event->getData());
        $this->resolve($data);
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
