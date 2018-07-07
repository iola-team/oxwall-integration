<?php

namespace Everywhere\Api\Contract\Subscription;

interface SubscriptionManagerInterface
{
    /**
     * @param string $query
     * @param mixed[] $variables
     */
    public function subscribe($query, $variables);

    /**
     * @return \Iterator
     */
    public function getIterator();
}
