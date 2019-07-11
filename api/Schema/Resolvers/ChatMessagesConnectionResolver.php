<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Schema\Relay\ConnectionResolver;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ConnectionObjectInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Contract\Schema\IDObjectInterface;
use Iola\Api\Contract\Integration\ChatRepositoryInterface;

class ChatMessagesConnectionResolver extends ConnectionResolver
{
    /**
     * @var DataLoaderInterface
     */
    protected $messagesLoader;

    /**
     * @var DataLoaderInterface
     */
    protected $messageCountsLoader;

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

        $this->messagesLoader = $loaderFactory->create(function($ids, $args) use($chatRepository) {
            return $chatRepository->findChatsMessageIds($ids, $args);
        });

        $this->messageCountsLoader = $loaderFactory->create(function($ids, $args) use($chatRepository) {
            return $chatRepository->countChatsMessages($ids, $args);
        });
    }

    private function buildArgs(array $arguments)
    {
        $arguments["filter"] = empty($arguments["filter"]) ? [] : $arguments["filter"];
        $notReadBy = empty($arguments["filter"]["notReadBy"]) 
            ? null
            : $arguments["filter"]["notReadBy"];

        $arguments["filter"]["notReadBy"] = $notReadBy instanceof IDObjectInterface
            ? $notReadBy->getId()
            : $notReadBy;

        return $arguments;
    }

    protected function getItems(ConnectionObjectInterface $connection, array $arguments)
    {
        $chatId = $connection->getRoot();

        return $this->messagesLoader->load($chatId, $this->buildArgs($arguments));
    }

    protected function getCount(ConnectionObjectInterface $connection, array $arguments)
    {
        $chatId = $connection->getRoot();

        return $this->messageCountsLoader->load($chatId, $this->buildArgs($arguments));
    }
}