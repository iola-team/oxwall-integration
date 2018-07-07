<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Everywhere\Api\Entities\Subscription;
use Everywhere\Api\Entities\SubscriptionEvent;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    protected $eventsTable;
    protected $subscriptionsTable;

    /**
     * @var \OW_Database
     */
    protected $dbo;

    public function __construct()
    {
        $this->eventsTable = OW_DB_PREFIX . "esapi_subscription_event";
        $this->subscriptionsTable = OW_DB_PREFIX . "esapi_subscription";

        $this->dbo = \OW::getDbo();
    }

    protected function findSubscriptionId($query, $variables)
    {
        return $this->dbo->queryForColumn(
            "SELECT `id` FROM `{$this->subscriptionsTable}` WHERE query=:query AND variables=:variables",
            [
                "query" => $query,
                "variables" => json_encode($variables)
            ]
        );
    }

    public function createSubscription($query, array $variables)
    {
        $existingSubscriptionId = $this->findSubscriptionId($query, $variables);

        /**
         * Reuse existing subscription if query and variables are the same
         */
        if ($existingSubscriptionId) {
            return $existingSubscriptionId;
        }

        return $this->dbo->insert(
            "INSERT INTO `{$this->subscriptionsTable}` SET query=:query, variables=:variables",
            [
                "query" => $query,
                "variables" => json_encode($variables)
            ]
        );
    }

    public function deleteSubscription($id)
    {
        $query = "DELETE FROM `{$this->subscriptionsTable}` WHERE `id`=:id";

        $this->dbo->delete($query, [
            "id" => $id
        ]);
    }

    public function findSubscriptionsByIds(array $ids)
    {
        if (empty($ids)) {
            return [];
        }

        $inClause = $this->dbo->mergeInClause($ids);
        $query = "SELECT * FROM `{$this->subscriptionsTable}` WHERE `id` IN ($inClause)";

        $list = $this->dbo->queryForList($query);

        $subscriptions = array_fill_keys($ids, null);
        foreach ($list as $record) {
            $subscription = new Subscription($record["id"]);
            $subscription->query = $record["query"];
            $subscription->variables = json_decode($record["variables"], true);

            $subscriptions[$record["id"]] = $subscription;
        }

        return $subscriptions;
    }


    public function findEvents($timeOffset)
    {
        $query = "SELECT * FROM `{$this->eventsTable}` WHERE `timeOffset` > :timeOffset";

        $list = $this->dbo->queryForList($query, [
            "timeOffset" => $timeOffset
        ]);

        $events = [];
        foreach ($list as $record) {
            $event = new SubscriptionEvent($record["id"]);
            $event->name = $record["name"];
            $event->timeOffset = $record["timeOffset"];
            $event->data = json_decode($record["data"], true);

            $events[] = $event;
        }

        return $events;
    }

    public function deleteEvents($beforeTimeOffset)
    {
        $query = "DELETE FROM `{$this->eventsTable}` WHERE `timeOffset` < :timeOffset";

        return $this->dbo->delete($query, [
            "timeOffset" => $beforeTimeOffset
        ]);
    }

    public function addEvent($name, $data, $timeOffset)
    {
        $query = "INSERT INTO `{$this->eventsTable}` SET `name` = :name, `timeOffset` = :timeOffset, `data` = :data";

        return $this->dbo->insert($query, [
            "name" => $name,
            "timeOffset" => $timeOffset,
            "data" => json_encode($data)
        ]);
    }
}
