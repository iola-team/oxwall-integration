<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Entities\Message;
use Everywhere\Api\Schema\EntityResolver;

class MessageResolver extends EntityResolver
{
    public function __construct(ChatRepositoryInterface $chatRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function($ids) use($chatRepository) {
            return $chatRepository->findMessagesByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver("user", function(Message $message) {
            return $message->userId;
        });

        $this->addFieldResolver("chat", function(Message $message) {
            return $message->chatId;
        });
    }
}
