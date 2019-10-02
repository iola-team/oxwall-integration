<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\PhotoRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
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
            "addUserPhoto" => function($root, $args, ContextInterface $context) use ($photoRepository, $edgeFactory) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();

                if ($userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                unset($input['userId']);
                $photoId = $photoRepository->addUserPhoto($userId, $input);

                return [
                    "node" => $photoId,
                    "user" => $userId,
                    "edge" => $edgeFactory->createFromArguments($args, $photoId)
                ];
            },

            "addPhotoComment" => function($root, $args, ContextInterface $context) use ($photoRepository) {
                $input = $args["input"];
                $userId = $input["userId"]->getId();

                if ($userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

                unset($input['userId']);
                $commentId = $photoRepository->addComment($userId, $input);

                return [
                    "node" => $commentId,
                    "user" => $userId,
                ];
            },

            "deleteUserPhoto" => function($root, $args, ContextInterface $context) use ($photoRepository) {
                $realId = $args["id"]->getId();

                /**
                 * @var $photo Photo
                 */
                $photo = $photoRepository->findByIds([$realId])[$realId];

                if ($photo->userId !== $context->getViewer()->getUserId()) {
                    throw new PermissionError();
                }

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
