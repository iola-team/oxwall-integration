<?php

namespace Iola\Api\Contract\Schema;

interface SubscriptionFactoryInterface
{
    /**
     * @param string|string[] $eventName
     * @param callable $filter
     * @param callable $filter
     *
     * @return SubscriptionInterface
     */
    public function create($eventName, callable $filter = null, callable $resolve = null);
}
