<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Entities\Message;
use Everywhere\Api\Schema\CompositeResolver;
use Everywhere\Api\Schema\IDObject;
use Everywhere\Api\Schema\Relay\EdgeFactory;

class MessageMutationResolver extends CompositeResolver
{
    /**
     * @var ChatRepositoryInterface
     */
    protected $chatRepository;

    /**
     * @var EdgeFactory
     */
    protected $edgeFactory;

    public function __construct(ChatRepositoryInterface $chatRepository, EdgeFactory $edgeFactory)
    {
        parent::__construct([
            "addMessage" => [$this, "addMessage"],
        ]);

        $this->chatRepository = $chatRepository;
        $this->edgeFactory = $edgeFactory;
    }

    public function addMessage($root, $args)
    {
        $input = $args["input"];
        $chatId = empty($input["chatId"]) ? null : $input["chatId"]->getId();
        $recipientIds = empty($input["recipientIds"]) ? null : array_map(function($idObject) {
            return $idObject->getId();
        }, $input["recipientIds"]);

        $messageId = $this->chatRepository->addMessage([
            "userId" => $input["userId"]->getId(),
            "content" => $input["content"],
            "chatId" => $chatId,
            "recipientIds" => $recipientIds,
        ]);

        /**
         * @var $message Message
         */
        $message = $this->chatRepository->findMessagesByIds([$messageId])[$messageId];

        return [
            "user" => $message->userId,
            "chat" => $message->chatId,
            "node" => $message,
            "edge" => function() use ($args, $message) {
                return $this->edgeFactory->createFromArguments($args, $message);
            }
        ];
    }
}
