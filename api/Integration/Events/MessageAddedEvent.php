<?php

namespace Iola\Api\Integration\Events;

use Iola\Api\App\Event;
use Iola\Api\Contract\Integration\Events\SubscriptionEventInterface;

class MessageAddedEvent extends Event implements SubscriptionEventInterface
{
    const EVENT_NAME = "messages.added";

    private $messageId = null;

    public function __construct($messageId)
    {
        parent::__construct(self::EVENT_NAME);

        $this->messageId = $messageId;
    }

    public function getMessageId()
    {
        return $this->messageId;
    }

    public function getData()
    {
        return [
            "messageId" => $this->messageId
        ];
    }
}
