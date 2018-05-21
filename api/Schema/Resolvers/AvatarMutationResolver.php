<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\AvatarRepositoryInterface;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;

class AvatarMutationResolver extends CompositeResolver
{
    public function __construct(AvatarRepositoryInterface $profileRepository)
    {
        parent::__construct([
            "addUserAvatar" => function($root, $args) use ($profileRepository) {
                $avatar = $profileRepository->addAvatar([
                    "userId" => $args["userId"]->getId(),
                    "file" => $args["file"]
                ]);

                return [
                    "node" => $avatar,
                    "user" => $args["userId"],
                ];
            },

            "deleteUserAvatar" => function($root, $args) use ($profileRepository) {
                $realId = $args["id"]->getId();
                $avatar = $profileRepository->findByIds([$realId])[$realId];

                $profileRepository->deleteAvatar([
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
