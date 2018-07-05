<?php

namespace Everywhere\Api\Contract\Schema;

interface SubscriptionInterface
{
    /**
     * @return \Iterator
     */
    public function subscribe();
}
