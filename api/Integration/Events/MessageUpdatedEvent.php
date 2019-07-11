<?php

namespace Iola\Api\Integration\Events;

class MessageUpdatedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "messages.updated";

    public function __construct($messageId)
    {
        parent::__construct(self::EVENT_NAME, [
            "messageId" => $messageId
        ]);
    }
}
