<?php

namespace Everywhere\Api\Contract\Subscription;

use Everywhere\Api\Contract\App\EventManagerInterface;

interface SubscriptionManagerFactoryInterface
{
    /**
     * @param EventManagerInterface $eventManager
     * @return SubscriptionManagerInterface
     */
    public function create(EventManagerInterface $eventManager);
}
