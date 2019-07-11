<?php

namespace Iola\Api\Integration\Events;

class MessageAddedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "messages.added";

    public function __construct($messageId)
    {
        parent::__construct(self::EVENT_NAME, [
            "messageId" => $messageId
        ]);
    }
}
