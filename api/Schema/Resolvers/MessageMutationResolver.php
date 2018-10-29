<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Entities\Message;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;
use Everywhere\Api\Schema\Relay\EdgeFactory;

class MessageMutationResolver extends CompositeResolver
{
    public function __construct(ChatRepositoryInterface $chatRepository, EdgeFactory $edgeFactory)
    {
        parent::__construct([
            "addMessage" => function($root, $args) use ($chatRepository, $edgeFactory) {
                $message = $chatRepository->addMessage([
                    "userId" => $args["input"]["userId"]->getId(),
                    "chatId" => $args["input"]["chatId"]->getId(),
                    "content" => $args["input"]["content"]
                ]);

                return [
                    "user" => $args["input"]["userId"],
                    "chat" => $args["input"]["chatId"],
                    "node" => $message,
                    "edge" => function() use ($edgeFactory, $args, $message) {
                        return $edgeFactory->createFromArguments($args, $message);
                    }
                ];
            },
        ]);
    }
}
