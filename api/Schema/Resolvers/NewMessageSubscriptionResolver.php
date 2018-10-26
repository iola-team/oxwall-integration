<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Integration\Events\NewMessageEvent;
use Everywhere\Api\Schema\IDObject;
use Everywhere\Api\Schema\SubscriptionResolver;

class NewMessageSubscriptionResolver extends SubscriptionResolver
{
    public function __construct(SubscriptionFactoryInterface $subscriptionFactory)
    {
        parent::__construct();

        $this->addFieldResolver("onMessageAdd", function($root, $args) use ($subscriptionFactory) {
            /**
             * @var $chatIdObject IDObject
             */
            $chatIdObject = $args["chatId"];

            return $subscriptionFactory->create(
                NewMessageEvent::EVENT_NAME,
                function ($data) use ($chatIdObject) {
                    return $data["chatId"] == $chatIdObject->getId();
                },
                function ($data) {
                    return [
                        "node" => $data["messageId"],
                        "user" => $data["userId"],
                        "chat" => $data["chatId"],
                        "edge" => [
                            "cursor" => "tmp-cursor", // TODO: use real cursor
                            "node" => $data["messageId"]
                        ]
                    ];
                }
            );
        });
    }
}
