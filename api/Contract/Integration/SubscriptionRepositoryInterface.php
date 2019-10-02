<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Contract\Integration;

use Iola\Api\Entities\Subscription;
use Iola\Api\Entities\SubscriptionEvent;

interface SubscriptionRepositoryInterface
{
    /**
     * Creates subscription and returns its id
     * @param string $streamId
     * @param string $query
     * @param mixed[] $variables
     *
     * @return mixed
     */
    public function createSubscription($streamId, $query, array $variables);

    /**
     * @param $subscriptionId
     */
    public function deleteSubscription($subscriptionId);

    /**
     * @param string $streamId
     *
     * @return Subscription[]
     */
    public function findSubscriptionsByStreamId($streamId);

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
