<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Schema\EntityResolver;

class ChatResolver extends EntityResolver
{
    public function __construct(ChatRepositoryInterface $chatRepository, DataLoaderFactoryInterface $loaderFactory)
    {
        $entityLoader = $loaderFactory->create(function($ids) use($chatRepository) {
            return $chatRepository->findChatsByIds($ids);
        });

        parent::__construct($entityLoader);

        $this->addFieldResolver("user", function(Chat $chat) {
            return $chat->userId;
        });
    }
}
