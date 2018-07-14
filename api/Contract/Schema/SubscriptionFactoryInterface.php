<?php

namespace Everywhere\Api\Contract\Schema;

interface SubscriptionFactoryInterface
{
    /**
     * @param string|string[] $eventName
     * @param callable $filter
     *
     * @return SubscriptionInterface
     */
    public function create($eventName, callable $filter = null);
}
