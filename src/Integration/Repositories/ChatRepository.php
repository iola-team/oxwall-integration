<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Entities\Chat;
use Everywhere\Api\Entities\Message;
use Everywhere\Api\Entities\User;

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
            $message->content = $messageDto->text;

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
        $count = $args["count"];
        $afterTimestamp = empty($args["after"]) ? 0 : $args["after"]->getTimestamp();

        $out = [];
        foreach ($chatIds as $id) {
            $out[$id] = [];

            /**
             * @var $messageDtos \MAILBOX_BOL_Message[]
             */
            $messageDtos = $this->messageDao->findListByConversationId($id, $count, $afterTimestamp);
            foreach ($messageDtos as $messageDto) {
                $out[$id][] = $messageDto->id;
            }
        }

        return $out;
    }

    public function countChatsMessages($chatIds, $args)
    {
        $afterTimestamp = empty($args["after"]) ? 0 : $args["after"]->getTimestamp();

        $out = [];
        foreach ($chatIds as $id) {
            $example = new \OW_Example();
            $example->andFieldEqual("conversationId", $id);
            $example->andFieldGreaterThan("timeStamp", $afterTimestamp);

            $out[$id] = $this->messageDao->countByExample($example);
        }

        return $out;
    }
}
