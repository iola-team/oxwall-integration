<?php

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Iola\Api\Contract\Integration\SubscriptionRepositoryInterface;
use Iola\Api\Entities\Subscription;
use Iola\Api\Entities\SubscriptionEvent;

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

    protected function findSubscriptionId($streamId, $query, array $variables)
    {
        $sql = "SELECT `id` FROM `{$this->subscriptionsTable}`
                    WHERE `streamId` = :streamId AND `query` = :query AND `variables` = :variables";

        return $this->dbo->queryForColumn(
            $sql,
            [
                "streamId" => $streamId,
                "query" => $query,
                "variables" => json_encode($variables)
            ]
        );
    }

    public function createSubscription($streamId, $query, array $variables)
    {
        $existingSubscriptionId = $this->findSubscriptionId($streamId, $query, $variables);

        if ($existingSubscriptionId) {
            return $existingSubscriptionId;
        }

        return $this->dbo->insert(
            "INSERT INTO `{$this->subscriptionsTable}` SET streamId=:streamId, query=:query, variables=:variables",
            [
                "streamId" => $streamId,
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

    public function findSubscriptionsByStreamId($streamId)
    {
        $query = "SELECT * FROM `{$this->subscriptionsTable}` WHERE `streamId` = :streamId";

        $list = $this->dbo->queryForList($query, [
            "streamId" => $streamId
        ]);

        $subscriptions = [];
        foreach ($list as $record) {
            $subscription = new Subscription($record["id"]);
            $subscription->streamId = $record["streamId"];
            $subscription->query = $record["query"];
            $subscription->variables = json_decode($record["variables"], true);

            $subscriptions[] = $subscription;
        }

        return $subscriptions;
    }


    public function findEvents($timeOffset)
    {
        $query = "SELECT * FROM `{$this->eventsTable}` WHERE `timeOffset` > :timeOffset ORDER BY `timeOffset`";

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
