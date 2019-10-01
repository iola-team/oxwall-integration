<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Iola\Api\Contract\Schema\SubscriptionInterface;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\PromiseAdapter;

class Subscription extends SyncPromise implements SubscriptionInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var PromiseAdapter
     */
    protected $promiseAdapter;

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
        EventManagerInterface $eventManager,
        PromiseAdapter $promiseAdapter
    )
    {
        $this->promiseAdapter = $promiseAdapter;
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

        $filterPromise = $this->promiseAdapter->createFulfilled(
            is_callable($this->filter) ? call_user_func($this->filter, $event->getData()) : true
        );

        $filterPromise->then(function($allowed) use($event) {
            if (!$allowed) {
                return;
            }

            $data = $this->resolveValue($event->getData());
            $this->resolve($data);
        });
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
