<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Contract\Integration\FriendshipRepositoryInterface;
use Everywhere\Api\Entities\Friendship;

class FriendMutationResolver extends CompositeResolver
{
    public function __construct(FriendshipRepositoryInterface $friendshipRepository)
    {
        parent::__construct([
            "addFriend" => function($root, $args) use ($friendshipRepository) {
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

                $friendshipId = $friendship
                    ? $friendshipRepository->updateFriendship($friendship->id, $status)
                    : $friendshipRepository->createFriendship($userId, $friendId, $status);

                return [
                    "user" => $userId,
                    "friend" => $friendId,
                    "friendship" => $friendshipId
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
