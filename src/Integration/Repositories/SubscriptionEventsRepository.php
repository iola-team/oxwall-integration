<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\Events\SubscriptionEventInterface;
use Everywhere\Api\Contract\Integration\SubscriptionEventsRepositoryInterface;
use Everywhere\Api\Entities\SubscriptionEvent;

class SubscriptionEventsRepository implements SubscriptionEventsRepositoryInterface
{
    protected $tableName;

    public function __construct()
    {
        $this->tableName = OW_DB_PREFIX . "esapi_subscription_event";
    }

    public function findEvents($timeOffset)
    {
        $query = "SELECT * FROM `{$this->tableName}` WHERE `timeOffset` > :timeOffset";

        $list = \OW::getDbo()->queryForList($query, [
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
        $query = "DELETE FROM `{$this->tableName}` WHERE `timeOffset` < :timeOffset";

        return \OW::getDbo()->delete($query, [
            "timeOffset" => $beforeTimeOffset
        ]);
    }

    public function addEvent($name, $data, $timeOffset)
    {
        $query = "INSERT INTO `{$this->tableName}` SET `name` = :name, `timeOffset` = :timeOffset, `data` = :data";

        return \OW::getDbo()->insert($query, [
            "name" => $name,
            "timeOffset" => $timeOffset,
            "data" => json_encode($data)
        ]);
    }
}
