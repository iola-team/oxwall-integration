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

                $friendshipId = $friendshipRepository->findFriendshipId($userId, $friendId);
                $autoStatus = $friendshipId ? Friendship::STATUS_ACTIVE : Friendship::STATUS_PENDING;
                $status = empty($input["status"]) ? $autoStatus : $input["status"];

                $friendshipId = $friendshipId
                    ? $friendshipRepository->updateFriendship($friendshipId, $status)
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

                $friendshipId = $friendshipRepository->findFriendshipId($userId, $friendId);

                if ($friendshipId) {
                    $friendshipRepository->deleteByIds([$friendshipId]);
                }

                return [
                    "deletedId" => $friendshipId,
                    "user" => $userId,
                    "friend" => $friendId
                ];
            }
        ]);
    }
}
