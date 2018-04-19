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
                return $avatarRepository->addAvatar([
                    "userId" => $args["userId"]->getId(),
                    "file" => $args["file"]
                ]);
            },

            "deleteUserAvatar" => function($root, $args) use ($avatarRepository) {
                return $avatarRepository->deleteAvatar([
                    "id" => $args["id"]->getId()
                ]);
            }
        ]);
    }
}
