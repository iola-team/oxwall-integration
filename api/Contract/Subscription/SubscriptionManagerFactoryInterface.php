<?php

namespace Iola\Api\Contract\Subscription;

use Iola\Api\Contract\App\EventManagerInterface;

interface SubscriptionManagerFactoryInterface
{
    /**
     * @param EventManagerInterface $eventManager
     * @return SubscriptionManagerInterface
     */
    public function create(EventManagerInterface $eventManager);
}
