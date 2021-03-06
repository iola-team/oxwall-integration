<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Schema\SubscriptionResolver;
use Iola\Api\Contract\Schema\SubscriptionFactoryInterface;

use Iola\Api\Integration\Events\FriendshipAddedEvent;
use Iola\Api\Integration\Events\FriendshipUpdatedEvent;
use Iola\Api\Integration\Events\FriendshipDeletedEvent;
use Iola\Api\Contract\Integration\FriendshipRepositoryInterface;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;

class FriendshipSubscriptionResolver extends SubscriptionResolver
{
    /**
     * @var SubscriptionFactoryInterface
     */
    protected $subscriptionFactory;

    /**
     * @var FriendshipRepositoryInterface
     */
    protected $friendshipRespository;

    /**
     * @var EdgeFactoryInterface
     */
    protected $edgeFactory;

    public function __construct(
        FriendshipRepositoryInterface $friendshipRespository,
        SubscriptionFactoryInterface $subscriptionFactory,
        EdgeFactoryInterface $edgeFactory
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->friendshipRespository = $friendshipRespository;
        $this->edgeFactory = $edgeFactory;

        parent::__construct([
            "onFriendshipAdd" => $this->createSubscription(
                FriendshipAddedEvent::EVENT_NAME,
                function($userId, $friendId, $friendshipId) {
                    return [
                        "user" => $userId,
                        "friend" => $friendId,
                        "friendship" => $friendshipId,
                        "edge" => function($root, $args) use($friendId, $friendshipId) {
                            return $this->edgeFactory->createFromArguments($args, [
                                "node" => $friendId,
                                "friendship" => $friendshipId
                            ]);
                        }
                    ];
                }
            ),

            "onFriendshipUpdate" => $this->createSubscription(
                FriendshipUpdatedEvent::EVENT_NAME,
                function($userId, $friendId, $friendshipId) {
                    return [
                        "user" => $userId,
                        "friend" => $friendId,
                        "friendship" => $friendshipId,
                    ];
                }
            ),

            "onFriendshipDelete" => $this->createSubscription(
                FriendshipDeletedEvent::EVENT_NAME,
                function($userId, $friendId, $friendshipId) {
                    return [
                        "user" => $userId,
                        "friend" => $friendId,
                        "deletedId" => $friendshipId
                    ];
                }
            )
        ]);
    }

    protected function createSubscription($eventName, callable $createPayload)
    {
        return function($root, $args) use($eventName, $createPayload) {
            $userId = $args["userId"]->getId();

            return $this->subscriptionFactory->create(
                $eventName,
                function ($data) use ($userId) {
                    return in_array($userId, [
                        $data["userId"],
                        $data["friendId"]
                    ]);
                },
    
                function ($data) use($userId, $createPayload) {
                    $friendId = $userId == $data["userId"] ? $data["friendId"] : $data["userId"];
                    $friendshipId = empty($data["friendshipId"])
                        ? $this->friendshipRespository
                               ->findFriendship($data["userId"], $data["friendId"])->id
                        : $data["friendshipId"];

                    return $createPayload($userId, $friendId, $friendshipId);
                }
            );
        };
    }
}