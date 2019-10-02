<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
