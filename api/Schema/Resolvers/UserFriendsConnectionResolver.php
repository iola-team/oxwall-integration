<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Schema\Relay\ConnectionResolver;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionObjectInterface;
use Everywhere\Api\Contract\Integration\FriendshipRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\IDObjectInterface;
use Everywhere\Api\Entities\Friendship;

class UserFriendsConnectionResolver extends ConnectionResolver
{
    public function __construct(
        FriendshipRepositoryInterface $friendshipRepository,
        DataLoaderFactoryInterface $loaderFactory,
        EdgeFactoryInterface $edgeFactory
    ) {
        parent::__construct($edgeFactory);

        $this->friendshipListLoader = $loaderFactory->create(function($ids, $args, $context) use($friendshipRepository) {
            return $friendshipRepository->findByUserIds($ids, $args);
        }, []);

        $this->friendshipCountsLoader = $loaderFactory->create(function($ids, $args, $context) use($friendshipRepository) {
            return $friendshipRepository->countByUserIds($ids, $args);
        }, []);
    }

    /**
     * TODO: Get rid of this ugly conversion somehow. 
     * Perhaps it would be better to do such convertion on type resolving phase, 
     * since we usually do not need id types in resolver functions.
     * The only exception is `node` resolver.
     * 
     * @param IDObjectInterface[] $idObjects
     * @return string[]
     */
    private function convertIdObjectsToLocalIds($idObjects)
    {
        return array_map(function(IDObjectInterface $idObject) {
            return $idObject->getId();
        }, $idObjects);
    }

    private function buildArgs(array $arguments)
    {
        $arguments["filter"]["friendIdIn"] = $this->convertIdObjectsToLocalIds(
            $arguments["filter"]["friendIdIn"]
        );

        return $arguments;
    }

    protected function getItems(ConnectionObjectInterface $connection, array $arguments)
    {
        $user = $connection->getRoot();

        return $this->friendshipListLoader
            ->load($user->id, $this->buildArgs($arguments))
            ->then(function($friendships) use($user) {
                return array_map(function(Friendship $friendship) use($user) {
                    return [
                        "node" => $friendship->userId == $user->id
                            ? $friendship->friendId 
                            : $friendship->userId,
                        "friendship" => $friendship
                    ];
                }, $friendships);
            }
        );
    }

    protected function getCount(ConnectionObjectInterface $connection, array $arguments)
    {
        return $this->friendshipCountsLoader->load(
            $connection->getRoot()->id,
            $this->buildArgs($arguments)
        );
    }
}