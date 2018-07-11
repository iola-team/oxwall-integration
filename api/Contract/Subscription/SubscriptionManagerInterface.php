<?php

namespace Everywhere\Api\Contract\Subscription;

interface SubscriptionManagerInterface
{
    /**
     * @param string $query
     * @param mixed[] $variables
     * @param string|null $subscriptionKey
     */
    public function subscribe($query, $variables, $subscriptionKey = null);

    /**
     * @return \Iterator
     */
    public function getIterator();
}
