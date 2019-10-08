<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\BlockRepositoryInterface;
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
        BlockRepositoryInterface $blockRepository,
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

        $blockedForLoader = $loaderFactory->create(function($userIds, $args) use($blockRepository) {
            return $blockRepository->hasBlockedUser($userIds, $args["for"]->getId());
        });

        $this->addFieldResolver("user", function(Chat $chat) {
            return $chat->userId;
        });

        $this->addFieldResolver("participants", function (Chat $chat) use($participantsLoader) {
            return $participantsLoader->load($chat->id);
        });

        $this->addFieldResolver("isBlocked", function(Chat $chat, $args) use($participantsLoader, $blockedForLoader) {
            return $participantsLoader->load($chat->id)
                ->then(function($participantIds) use($args, $blockedForLoader) {
                    return $blockedForLoader->loadMany($participantIds, $args)
                        ->then(function($blockedList) {
                            return count(array_filter($blockedList)) > 0;
                        });
                });
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
