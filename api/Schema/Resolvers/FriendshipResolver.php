<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\FriendshipRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Schema\EntityResolver;
use Everywhere\Api\Entities\Friendship;

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
