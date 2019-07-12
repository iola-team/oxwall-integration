<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Schema\Relay\ConnectionResolver;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ConnectionObjectInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Entities\User;

class UserChatsConnectionResolver extends ConnectionResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $chatsListLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $chatsCountLoader;

    /**
     * @var ConnectionFactoryInterface
     */
    protected $connectionFactory;

    public function __construct(
        ChatRepositoryInterface $chatRepository,
        DataLoaderFactoryInterface $loaderFactory,
        EdgeFactoryInterface $edgeFactory
    ) {
        parent::__construct($edgeFactory);

        $this->chatsListLoader = $loaderFactory->create(function($ids, $args, $context) use($chatRepository) {
            return $chatRepository->findChatIdsByUserIds($ids, $args);
        }, []);

        $this->chatsCountLoader = $loaderFactory->create(function($ids, $args, $context) use($chatRepository) {
            return $chatRepository->countChatsByUserIds($ids, $args);
        }, []);
    }

    /**
     * @param string $chatId
     * @param User $user
     * @return array
     */
    private function buildEdge($chatId, $user)
    {
        return [
            "node" => $chatId,
            "userId" => $user->id
        ];
    }

    protected function getItems(ConnectionObjectInterface $connection, array $arguments)
    {
        $user = $connection->getRoot();
        $edgeBuilder = function($chatId) use($user) {
            return $this->buildEdge($chatId, $user);
        };

        return $this->chatsListLoader
            ->load($user->id, $arguments)
            ->then(function($chats) use($edgeBuilder) {
                return array_map($edgeBuilder, $chats);
            }
        );
    }

    protected function getCount(ConnectionObjectInterface $connection, array $arguments)
    {
        return $this->chatsCountLoader->load($connection->getRoot()->id, $arguments);
    }
}