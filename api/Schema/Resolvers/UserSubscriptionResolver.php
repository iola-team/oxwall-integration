<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\UserRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Entities\User;
use Everywhere\Api\Integration\Events\UserApprovedEvent;
use Everywhere\Api\Integration\Events\UserEmailVerifiedEvent;
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

    /**
     * @var DataLoaderInterface
     */
    protected $userLoader;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SubscriptionFactoryInterface $subscriptionFactory,
        DataLoaderFactoryInterface $loaderFactory
    )
    {
        parent::__construct([
            "onUserApproved" => function($root, $args) {
                return $this->createSubscription($args, UserApprovedEvent::EVENT_NAME);
            },
            "onUserEmailVerified" => function($root, $args) {
                return $this->createSubscription($args, UserEmailVerifiedEvent::EVENT_NAME);
            },
        ]);

        $this->userRepository = $userRepository;
        $this->subscriptionFactory = $subscriptionFactory;

        $this->userLoader = $loaderFactory->create(function($ids) use($userRepository) {
            return $userRepository->findByIds($ids);
        });
    }

    protected function createSubscription(array $args, $eventName)
    {
        return $this->subscriptionFactory->create(
            $eventName,
            function ($data) use ($args) {
                return $this->loadUser($data)->then(function($user) use($args) {
                    return $this->filterEvents($user, $args);
                });
            },

            function ($data) use ($args) {
                return $this->loadUser($data)->then(function($user) use($args) {
                    return $this->createUserPayload($user, $args);
                });
            }
        );
    }

    protected function loadUser($data)
    {
        return $this->userLoader->load($data["userId"]);
    }

    protected function createUserPayload(User $user, array $args)
    {
        return ["user" => $user->id];
    }

    protected function filterEvents(User $user, array $args)
    {
        /**
         * @var $userIdObject IDObject
         */
        $userIdObject = $args["userId"];

        return $user->id == $userIdObject->getId();
    }
}
