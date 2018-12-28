<?php

namespace Everywhere\Api\Integration\Events;

class PhotoCommentAddedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "photoComment.added";

    public function __construct($commentId)
    {
        parent::__construct(self::EVENT_NAME, [
            "commentId" => $commentId
        ]);
    }
}
