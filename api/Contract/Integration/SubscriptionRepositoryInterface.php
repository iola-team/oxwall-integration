<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Entities\Subscription;
use Everywhere\Api\Entities\SubscriptionEvent;

interface SubscriptionRepositoryInterface
{
    /**
     * Creates subscription and returns its id
     *
     * @param string $query
     * @param mixed[] $variables
     *
     * @return mixed
     */
    public function createSubscription($query, array $variables);

    /**
     * @param mixed $id
     */
    public function deleteSubscription($id);

    /**
     * @param array $ids
     *
     * @return Subscription
     */
    public function findSubscriptionsByIds(array $ids);

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
