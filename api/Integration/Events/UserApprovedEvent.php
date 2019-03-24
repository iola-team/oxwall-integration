<?php

namespace Everywhere\Api\Integration\Events;

class UserApprovedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "user.approved";

    public function __construct($userId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
        ]);
    }
}
