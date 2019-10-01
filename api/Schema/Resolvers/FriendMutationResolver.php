<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Integration\FriendshipRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Entities\Friendship;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;

class FriendMutationResolver extends CompositeResolver
{
    public function __construct(
        FriendshipRepositoryInterface $friendshipRepository, 
        EdgeFactoryInterface $edgeFactory
    ) {
        parent::__construct([
            "addFriend" => function($root, $args, ContextInterface $context) use ($friendshipRepository, $edgeFactory) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();
                $friendId = $input["friendId"]->getId();

                if ($userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

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

            "deleteFriend" => function($root, $args, ContextInterface $context) use ($friendshipRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();
                $friendId = $input["friendId"]->getId();
                $friendship = $friendshipRepository->findFriendship($userId, $friendId);
                $friendshipOwners = [$friendship->friendId, $friendship->userId];
                $deletedId = null;

                if (!in_array($context->getViewer()->getUserId(), $friendshipOwners)) {
                    throw new PermissionError();
                }

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
