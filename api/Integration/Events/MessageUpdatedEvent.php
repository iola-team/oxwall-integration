<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
