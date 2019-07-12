<?php

namespace Iola\Api\Integration\Events;

class FriendshipDeletedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "friendship.deleted";

    public function __construct($userId, $friendId, $friendshipId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
            "friendId" => $friendId,
            "friendshipId" => $friendshipId,
        ]);
    }
}
