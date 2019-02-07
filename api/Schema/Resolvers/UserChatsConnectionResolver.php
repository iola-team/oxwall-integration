<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Schema\Relay\ConnectionResolver;
use Everywhere\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionObjectInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Entities\User;

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
        ConnectionFactoryInterface $connectionFactory,
        EdgeFactoryInterface $edgeFactory
    ) {
        parent::__construct($edgeFactory);

        $this->connectionFactory = $connectionFactory;

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
            "unreadMessages" => function($root, $arguments) use($chatId, $user) {
                return $this->connectionFactory->create($chatId, array_merge($arguments, [
                    "filter" => [
                        "notReadBy" => $user->id
                    ]
                ]));
            }
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