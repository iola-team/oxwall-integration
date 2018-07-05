<?php

namespace Everywhere\Api\Schema;

use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Schema\SubscriptionInterface;
use League\Event\ListenerAcceptorInterface;

class Subscription implements SubscriptionInterface
{
    /**
     * @var ListenerAcceptorInterface
     */
    protected $eventSource;

    /**
     * @var \SplQueue
     */
    protected $queue;

    protected $eventNames = [];

    public function __construct(array $eventNames, ListenerAcceptorInterface $eventSource)
    {
        $this->eventSource = $eventSource;
        $this->eventNames = $eventNames;

        $this->queue = new \SplQueue();
        $this->queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
    }

    /**
     * @param SubscriptionEventInterface $event
     */
    protected function handleEvent($event)
    {
        if (!$event instanceof SubscriptionEventInterface) {
            return;
        }

        $this->queue->enqueue($event->getData());
    }

    /**
     * @return \Iterator
     */
    public function subscribe()
    {
        foreach ($this->eventNames as $eventName) {
            $this->eventSource->addListener($eventName, function($event) {
                $this->handleEvent($event);
            });
        }

        return new \InfiniteIterator($this->queue);
    }
}
