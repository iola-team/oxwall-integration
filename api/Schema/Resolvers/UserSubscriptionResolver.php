<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Schema\SubscriptionFactoryInterface;
use Iola\Api\Integration\Events\UserUpdateEvent;
use Iola\Api\Schema\IDObject;
use Iola\Api\Schema\SubscriptionResolver;

class UserSubscriptionResolver extends SubscriptionResolver
{
    /**
     * @var SubscriptionFactoryInterface
     */
    protected $subscriptionFactory;

    public function __construct(
        SubscriptionFactoryInterface $subscriptionFactory
    )
    {
        parent::__construct([
            "onUserUpdate" => function ($root, $args) {
                return $this->createSubscription($args, UserUpdateEvent::EVENT_NAME);
            },
        ]);

        $this->subscriptionFactory = $subscriptionFactory;
    }

    protected function createSubscription(array $args, $eventName)
    {
        return $this->subscriptionFactory->create(
            $eventName,
            function ($data) use ($args) {
                return $this->filterEvents($data["userId"], $args);
            },

            function ($data) use ($args) {
                return [
                    "user" => $data["userId"]
                ];
            }
        );
    }

    protected function filterEvents($currentUserId, array $args)
    {
        /**
         * @var $userIdObject IDObject
         */
        $userIdObject = $args["userId"];

        return $currentUserId == $userIdObject->getId();
    }
}
