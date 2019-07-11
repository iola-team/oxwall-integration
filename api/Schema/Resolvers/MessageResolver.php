<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Entities\Message;
use Iola\Api\Schema\EntityResolver;

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
