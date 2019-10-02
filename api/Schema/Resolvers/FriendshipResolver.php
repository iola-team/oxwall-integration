<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\FriendshipRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Schema\EntityResolver;
use Iola\Api\Entities\Friendship;

class FriendshipResolver extends EntityResolver
{
    public function __construct(
        FriendshipRepositoryInterface $friendshipRepository, 
        DataLoaderFactoryInterface $loaderFactory
    ) {
        $entityLoader = $loaderFactory->create(function($ids) use($friendshipRepository) {
            return $friendshipRepository->findByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver("user", function(Friendship $friendship) {
            return $friendship->userId;
        });

        $this->addFieldResolver("friend", function(Friendship $friendship) {
            return $friendship->friendId;
        });
    }
}
