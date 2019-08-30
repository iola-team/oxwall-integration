<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Auth\Errors\PermissionError;
use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Contract\Schema\ContextInterface;
use Iola\Api\Entities\Message;
use Iola\Api\Schema\CompositeResolver;
use Iola\Api\Schema\IDObject;
use Iola\Api\Schema\Relay\EdgeFactory;

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
            "markMessagesAsRead" => [$this, "markMessagesAsRead"],
        ]);

        $this->chatRepository = $chatRepository;
        $this->edgeFactory = $edgeFactory;
    }

    public function addMessage($root, $args, ContextInterface $context)
    {
        $input = $args["input"];
        $userId = $input["userId"]->getId();

        if ($userId !== $context->getViewer()->getUserId()) {
            throw new PermissionError();
        }

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
            "edge" => $this->edgeFactory->createFromArguments($args, $message),
            "chatEdge" => function($root, $arguments) use($userId, $message) {
                return $this->edgeFactory->createFromArguments($arguments, [
                    "node" => $message->chatId,
                    "userId" => $userId
                ]);
            }
        ];
    }

    public function markMessagesAsRead($root, $args, ContextInterface $context)
    {
        $input = $args["input"];
        $userId = $input["userId"]->getId();

        if ($userId !== $context->getViewer()->getUserId()) {
            throw new PermissionError();
        }

        $messageIds = array_map(function(IDObject $idObject) {
            return $idObject->getId();
        }, $input["messageIds"]);

        $markedMessageIds = $this->chatRepository->markMessagesAsRead([
            "userId" => $userId,
            "messageIds" => $messageIds
        ]);

        /**
         * @var $message Message[]
         */
        $messages = $this->chatRepository->findMessagesByIds($markedMessageIds);

        $out = [];
        foreach ($messages as $message) {
            $out[] = [
                "user" => $message->userId,
                "chat" => $message->chatId,
                "node" => $message,
                "edge" => $this->edgeFactory->createFromArguments($args, $message),
                "chatEdge" => function($root, $arguments) use($userId, $message) {
                    return $this->edgeFactory->createFromArguments($arguments, [
                        "node" => $message->chatId,
                        "userId" => $userId
                    ]);
                }
            ];
        }

        return $out;
    }
}
