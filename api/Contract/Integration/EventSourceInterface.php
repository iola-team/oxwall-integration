<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Entities\SubscriptionEvent;
use League\Event\ListenerAcceptorInterface;

interface EventSourceInterface extends ListenerAcceptorInterface
{
    /**
     * Loads persisted events starting from given milliseconds offset and returns last event milliseconds offset or null
     *
     * @param int|null $timeOffset
     *
     * @return int|null
     */
    public function loadEvents($timeOffset = null);
}
