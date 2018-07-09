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

        $messagesLoader = $loaderFactory->create(function($ids, $args) use($chatRepository) {
            return $chatRepository->findChatsMessageIds($ids, $args);
        });

        $messageCountsLoader = $loaderFactory->create(function($ids, $args) use($chatRepository) {
            return $chatRepository->countChatsMessages($ids, $args);
        });

        $this->addFieldResolver("user", function(Chat $chat) {
            return $chat->userId;
        });

        $this->addFieldResolver("participants", function (Chat $chat) use($participantsLoader) {
            return $participantsLoader->load($chat->id);
        });

        $this->addFieldResolver(
            "messages",
            function (Chat $chat, $args) use($connectionFactory, $messagesLoader, $messageCountsLoader) {
                return $connectionFactory->create(
                    $chat,
                    $args,
                    function ($args) use($chat, $messagesLoader) {
                        return $messagesLoader->load($chat->id, $args);
                    },
                    function($args) use($chat, $messageCountsLoader) {
                        return $messageCountsLoader->load($chat->id, $args);
                    }
                );
            }
        );
    }
}