<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\AvatarRepositoryInterface;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;

class AvatarMutationResolver extends CompositeResolver
{
    public function __construct(AvatarRepositoryInterface $avatarRepository)
    {
        parent::__construct([
            "addUserAvatar" => function($root, $args) use ($avatarRepository) {
                $avatar = $avatarRepository->addAvatar([
                    "userId" => $args["userId"]->getId(),
                    "file" => $args["file"]
                ]);

                return [
                    "node" => $avatar,
                    "user" => $args["userId"],
                ];
            },

            "deleteUserAvatar" => function($root, $args) use ($avatarRepository) {
                $realId = $args["id"]->getId();
                $avatar = $avatarRepository->findByIds([$realId])[$realId];

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