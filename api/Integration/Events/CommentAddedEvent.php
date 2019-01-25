<?php

namespace Everywhere\Api\Integration\Events;

class CommentAddedEvent extends SubscriptionEvent
{
    const EVENT_NAME = "comment.added";

    public function __construct($commentId)
    {
        parent::__construct(self::EVENT_NAME, [
            "commentId" => $commentId,
        ]);
    }
}
