<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Contract\Schema\ConnectionFactoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
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

        $this->addFieldResolver("messages", function (
            Chat $chat, $args, ContextInterface $context
        ) use($connectionFactory, $participantsLoader) {

            return $participantsLoader
                ->load($chat->id)
                ->then(function($participantIds) use($chat, $args, $context, $connectionFactory) {
                    if (!in_array($context->getViewer()->getUserId(), $participantIds)) {
                        throw new PermissionError();
                    }
        
                    return $connectionFactory->create($chat->id, $args);
                });
        });
    }
}
