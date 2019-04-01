<?php

namespace Everywhere\Api\Integration\Events;

class UserUpdateEvent extends SubscriptionEvent
{
    const EVENT_NAME = "user.update";

    public function __construct($userId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
        ]);
    }
}
