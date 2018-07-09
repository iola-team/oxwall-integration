<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Entities\Message;

class ChatRepository implements ChatRepositoryInterface
{

    /**
     * @var \MAILBOX_BOL_ConversationService
     */
    protected $conversationService;

    /**
     * @var \MAILBOX_BOL_MessageDao
     */
    protected $messageDao;

    public function __construct()
    {
        $this->conversationService = \MAILBOX_BOL_ConversationService::getInstance();
        $this->messageDao = \MAILBOX_BOL_MessageDao::getInstance();
    }

    public function findChatsByIds($ids)
    {
        $out = [];
        foreach ($ids as $id) {
            $conversationDto = $this->conversationService->getConversation($id);

            $chat = new Chat($conversationDto->id);
            $chat->userId = $conversationDto->initiatorId;

            $out[$id] = $chat;
        }

        return $out;
    }

    public function findMessagesByIds($ids)
    {
        $out = [];
        foreach ($ids as $id) {
            $messageDto = $this->conversationService->getMessage($id);

            $message = new Message($messageDto->id);
            $message->userId = $messageDto->senderId;
            $message->chatId = $messageDto->conversationId;
            $message->createdAt = new \DateTime("@" . $messageDto->timeStamp);
            $message->content = [
                "text" => $messageDto->text
            ];

            $out[$id] = $message;
        }

        return $out;
    }


    public function findChatsParticipantIds($chatIds)
    {
        $out = [];
        foreach ($chatIds as $id) {
            $conversationDto = $this->conversationService->getConversation($id);

            $out[$id] = [$conversationDto->initiatorId, $conversationDto->interlocutorId];
        }

        return $out;
    }

    public function findChatsMessageIds($chatIds, $args)
    {
        $out = [];
        foreach ($chatIds as $id) {
            $out[$id] = [];

            $example = new \OW_Example();
            $example->andFieldEqual("conversationId", $id);
            $example->setLimitClause($args["offset"], $args["count"]);

            $example->setOrder('timeStamp DESC');

            /**
             * @var $messageDtos \MAILBOX_BOL_Message[]
             */
            $messageDtos = $this->messageDao->findListByExample($example);

            foreach ($messageDtos as $messageDto) {
                $out[$id][] = $messageDto->id;
            }
        }

        return $out;
    }

    public function countChatsMessages($chatIds, $args)
    {
        $out = [];
        foreach ($chatIds as $id) {
            $out[$id] = $this->messageDao->findCountByConversationId($id);
        }

        return $out;
    }

    public function addMessage($args)
    {
        $userId = $args["userId"];
        $chatId = $args["chatId"];
        $content = $args["content"];

        $chat = $this->conversationService->getConversation($chatId);
        $messageDto = $this->conversationService->addMessage($chat, $userId, $content["text"]);

        return $messageDto->id;
    }
}