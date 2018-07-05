<?php

namespace Everywhere\Api\Contract\Integration\Events;

use Everywhere\Api\Contract\App\EventInterface;

interface SubscriptionEventInterface extends EventInterface
{
    /**
     * @return mixed
     */
    public function getData();
}
