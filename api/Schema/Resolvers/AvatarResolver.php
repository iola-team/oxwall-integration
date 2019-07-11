<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\AvatarRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\Avatar;
use Iola\Api\Schema\EntityResolver;

class AvatarResolver extends EntityResolver
{
    public function __construct(AvatarRepositoryInterface $avatarRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function ($ids, $args) use ($avatarRepository) {
            return $avatarRepository->findByIds($ids, $args);
        });

        $urlLoader = $loaderFactory->create(function($ids, $args) use ($avatarRepository) {
            return $avatarRepository->getUrls($ids, $args);
        });

        parent::__construct($entityLoader, [
            "url" => function(Avatar $avatar, $args) use ($urlLoader) {
                return $urlLoader->load($avatar->id, $args);
            },

            "user" => function(Avatar $avatar) {
                return $avatar->userId;
            }
        ]);
    }
}
