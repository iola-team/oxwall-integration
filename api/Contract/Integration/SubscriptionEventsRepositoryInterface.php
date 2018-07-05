<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Entities\SubscriptionEvent;

interface SubscriptionEventsRepositoryInterface
{
    /**
     * @param int $afterTimeOffset
     *
     * @return SubscriptionEvent[]
     */
    public function findEvents($afterTimeOffset);

    /**
     * @param int $beforeTimeOffset
     */
    public function deleteEvents($beforeTimeOffset);

    /**
     * @param string $name
     * @param string $data
     * @param int $timeOffset
     *
     * @return string
     */
    public function addEvent($name, $data, $timeOffset);
}
