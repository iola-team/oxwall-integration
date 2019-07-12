<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\Chat;
use Iola\Api\Schema\EntityResolver;

class ChatResolver extends EntityResolver
{
    public function __construct(
        ChatRepositoryInterface $chatRepository,
        DataLoaderFactoryInterface $loaderFactory,
        ConnectionFactoryInterface $connectionFactory
    ) {
        $entityLoader = $loaderFactory->create(function($ids) use($chatRepository) {
            return $chatRepository->findChatsByIds($ids);
        });

        parent::__construct($entityLoader);

        $participantsLoader = $loaderFactory->create(function($ids) use($chatRepository) {
            return $chatRepository->findChatsParticipantIds($ids);
        });

        $this->addFieldResolver("user", function(Chat $chat) {
            return $chat->userId;
        });

        $this->addFieldResolver("participants", function (Chat $chat) use($participantsLoader) {
            return $participantsLoader->load($chat->id);
        });

        $this->addFieldResolver("messages", function (Chat $chat, $args) use($connectionFactory) {
            return $connectionFactory->create($chat->id, $args);
        });
    }
}
