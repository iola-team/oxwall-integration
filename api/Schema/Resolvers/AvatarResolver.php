<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
