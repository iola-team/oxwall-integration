<?php

namespace Iola\Api\Integration\Events;

class FriendshipUpdatedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "friendship.updated";

    public function __construct($userId, $friendId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
            "friendId" => $friendId,
        ]);
    }
}
