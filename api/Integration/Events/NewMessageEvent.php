<?php

namespace Everywhere\Api\Integration\Events;

class NewMessageEvent extends SubscriptionEvent
{
    const EVENT_NAME = "messages.new";

    public function __construct($userId, $chatId, $messageId)
    {
        parent::__construct(self::EVENT_NAME, [
            "userId" => $userId,
            "chatId" => $chatId,
            "messageId" => $messageId
        ]);
    }
}
