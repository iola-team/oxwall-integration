<?php

namespace Iola\Api\Integration\Events;

class FriendshipAddedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "friendship.added";

    public function __construct($userId, $friendId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
            "friendId" => $friendId,
        ]);
    }
}
