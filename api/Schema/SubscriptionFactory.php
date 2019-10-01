<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema;

use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Schema\SubscriptionFactoryInterface;
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
