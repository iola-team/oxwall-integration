<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Integration\FriendshipRepositoryInterface;
use Iola\Api\Entities\Friendship;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;

class FriendMutationResolver extends CompositeResolver
{
    public function __construct(
        FriendshipRepositoryInterface $friendshipRepository, 
        EdgeFactoryInterface $edgeFactory
    ) {
        parent::__construct([
            "addFriend" => function($root, $args) use ($friendshipRepository, $edgeFactory) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();
                $friendId = $input["friendId"]->getId();

                $friendship = $friendshipRepository->findFriendship($userId, $friendId);
                $autoStatus = Friendship::STATUS_PENDING;
                if ($friendship)
                {
                    $canAccept = (
                        $friendship->status === Friendship::STATUS_PENDING 
                        && 
                        $friendship->friendId === $userId
                    );

                    $autoStatus = $canAccept ? Friendship::STATUS_ACTIVE : $friendship->status;
                }

                $status = empty($input["status"]) ? $autoStatus : $input["status"];

                if (!$friendship && $status === Friendship::STATUS_IGNORED) {
                    throw new \InvalidArgumentException(
                        "New friendship can not be created with status `$status`"
                    );
                }

                $friendshipId = $friendship
                    ? $friendshipRepository->updateFriendship($friendship->id, $status)
                    : $friendshipRepository->createFriendship($userId, $friendId, $status);

                return [
                    "user" => $userId,
                    "friend" => $friendId,
                    "friendship" => $friendshipId,
                    "edge" => $edgeFactory->createFromArguments($args, [
                        "node" => $friendId,
                        "friendship" => $friendshipId
                    ])
                ];
            },

            "deleteFriend" => function($root, $args) use ($friendshipRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();
                $friendId = $input["friendId"]->getId();

                $friendship = $friendshipRepository->findFriendship($userId, $friendId);
                $deletedId = null;

                if ($friendship) {
                    $friendshipRepository->deleteByIds([$friendship->id]);
                    $deletedId = $friendship->id;
                }

                return [
                    "deletedId" => $deletedId,
                    "user" => $userId,
                    "friend" => $friendId
                ];
            }
        ]);
    }
}
