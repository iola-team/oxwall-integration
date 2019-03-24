<?php

namespace Everywhere\Api\Integration\Events;

class UserEmailVerifiedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "user.emailVerified";

    public function __construct($userId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
        ]);
    }
}
