<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Integration\Events\UserUpdateEvent;
use Everywhere\Api\Schema\IDObject;
use Everywhere\Api\Schema\SubscriptionResolver;

class UserSubscriptionResolver extends SubscriptionResolver
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var SubscriptionFactoryInterface
     */
    protected $subscriptionFactory;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SubscriptionFactoryInterface $subscriptionFactory
    )
    {
        parent::__construct([
            "onUserUpdate" => function ($root, $args) {
                return $this->createSubscription($args, UserUpdateEvent::EVENT_NAME);
            },
        ]);

        $this->userRepository = $userRepository;
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
