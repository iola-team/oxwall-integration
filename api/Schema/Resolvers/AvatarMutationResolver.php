<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\AvatarRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Schema\CompositeResolver;

class AvatarMutationResolver extends CompositeResolver
{
    public function __construct(AvatarRepositoryInterface $avatarRepository)
    {
        parent::__construct([
            "addUserAvatar" => function($root, $args, ContextInterface $context) use ($avatarRepository) {
                $userId = $args["userId"]->getId();

                if ($userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                $avatar = $avatarRepository->addAvatar([
                    "userId" => $args["userId"]->getId(),
                    "file" => $args["file"]
                ]);

                return [
                    "node" => $avatar,
                    "user" => $args["userId"],
                ];
            },

            "deleteUserAvatar" => function($root, $args, ContextInterface $context) use ($avatarRepository) {
                $avatarId = $args["id"]->getId();
                $avatar = $avatarRepository->findByIds([$avatarId])[$avatarId];

                if ($avatar->userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                $avatarRepository->deleteAvatar([
                    "id" => $avatar->id
                ]);

                return [
                    "deletedId" => $args["id"],
                    "user" => $avatar->userId
                ];
            }
        ]);
    }
}
