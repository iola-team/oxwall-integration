<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Contract\Schema\ConnectionFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Schema\EntityResolver;

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
