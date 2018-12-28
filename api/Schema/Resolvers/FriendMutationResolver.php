<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Contract\Integration\FriendshipRepositoryInterface;

class FriendMutationResolver extends CompositeResolver
{
    public function __construct(FriendshipRepositoryInterface $friendshipRepository)
    {
        parent::__construct([
            "addFriend" => function($root, $args) use ($friendshipRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();
                $friendId = $input["friendId"]->getId();
                $status = $input["status"];

                $friendshipId = $friendshipRepository->findFriendshipId($userId, $friendId);
                $friendshipId = $friendshipId
                    ? $friendshipRepository->updateFriendship($friendshipId, $status)
                    : $friendshipRepository->createFriendship($userId, $friendId, $status);

                return [
                    "user" => $userId,
                    "friend" => $friendId,
                    "friendship" => $friendship
                ];
            },

            "deleteFriend" => function($root, $args) use ($friendshipRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();
                $friendId = $input["friendId"]->getId();

                $friendship = $friendshipRepository->findFriendship($userId, $friendId);
                $friendshipRepository->deleteByIds([
                    $friendship->id
                ]);

                return [
                    "deletedId" => $friendship->id,
                    "user" => $userId,
                    "friend" => $friendId
                ];
            }
        ]);
    }
}
