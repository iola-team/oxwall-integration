<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\PhotoRepositoryInterface;
use Everywhere\Api\Entities\Photo;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;

class PhotoMutationResolver extends CompositeResolver
{
    public function __construct(PhotoRepositoryInterface $photoRepository)
    {
        parent::__construct([
            "addUserPhoto" => function($root, $args) use ($photoRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();

                unset($input['userId']);
                $photoId = $photoRepository->addUserPhoto($userId, $input);

                return [
                    "node" => $photoId,
                    "user" => $userId,
                ];
            },

            "deleteUserPhoto" => function($root, $args) use ($photoRepository) {
                $realId = $args["id"]->getId();

                /**
                 * @var $photo Photo
                 */
                $photo = $photoRepository->findByIds([$realId])[$realId];
                $photoRepository->deleteByIds([
                    $photo->id
                ]);

                return [
                    "deletedId" => $photo->id,
                    "user" => $photo->userId,
                ];
            }
        ]);
    }
}
