<?php

namespace Iola\Api\Contract\Integration\Events;

use Iola\Api\Contract\App\EventInterface;

interface SubscriptionEventInterface extends EventInterface
{
    /**
     * @return mixed
     */
    public function getData();
}
