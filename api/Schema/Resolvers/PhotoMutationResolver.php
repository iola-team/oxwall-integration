<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\PhotoRepositoryInterface;
use Iola\Api\Entities\Photo;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;

class PhotoMutationResolver extends CompositeResolver
{
    public function __construct(
        PhotoRepositoryInterface $photoRepository,
        EdgeFactoryInterface $edgeFactory
    ) {
        parent::__construct([
            "addUserPhoto" => function($root, $args) use ($photoRepository, $edgeFactory) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();

                unset($input['userId']);
                $photoId = $photoRepository->addUserPhoto($userId, $input);

                return [
                    "node" => $photoId,
                    "user" => $userId,
                    "edge" => $edgeFactory->createFromArguments($args, $photoId)
                ];
            },

            "addPhotoComment" => function($root, $args) use ($photoRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();

                unset($input['userId']);
                $commentId = $photoRepository->addComment($userId, $input);

                return [
                    "node" => $commentId,
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
