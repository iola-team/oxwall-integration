<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Entities\Chat;
use Iola\Api\Entities\Message;

class ChatRepository implements ChatRepositoryInterface
{

    /**
     * @var \MAILBOX_BOL_ConversationService
     */
    protected $conversationService;

    /**
     * @var \MAILBOX_BOL_ConversationDao
     */
    protected $conversationDao;

    /**
     * @var \MAILBOX_BOL_MessageDao
     */
    protected $messageDao;

    public function __construct()
    {
        $this->conversationService = \MAILBOX_BOL_ConversationService::getInstance();
        $this->conversationDao = \MAILBOX_BOL_ConversationDao::getInstance();
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

    public function findChatIdsByUserIds($ids, array $args)
    {
        $activeModes = $this->conversationService->getActiveModeList();

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = [];
            $conversationInfoList = $this->conversationDao->findConversationItemListByUserId(
                $id, $activeModes, $args["offset"], $args["count"]
            );

            foreach ($conversationInfoList as $conversationInfo) {
                $out[$id][] = $conversationInfo["id"];
            }
        }

        return $out;
    }

    public function countChatsByUserIds($ids, array $args)
    {
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = $this->conversationService->countConversationListByUserId($id);
        }

        return $out;
    }

    public function findMessagesByIds($ids)
    {
        $userService = \BOL_UserService::getInstance();

        /**
         * @var $attachmentsByMessagesIds \MAILBOX_BOL_Attachment[]
         */
        $attachmentsByMessagesIds = $this->conversationService->findAttachmentsByMessageIdList($ids);

        $out = [];
        foreach ($ids as $id) {
            /**
             * @var $messageDto \MAILBOX_BOL_Message
             */
            $messageDto = $this->conversationService->getMessage($id);

            $image = null;
            if (array_key_exists($messageDto->id, $attachmentsByMessagesIds)) {
                $attachment = $attachmentsByMessagesIds[$messageDto->id][0];
                $ext = \UTIL_File::getExtension($attachment->fileName);
                $fileName = $this->conversationService->getAttachmentFileName($attachment->id, $attachment->hash, $ext, $attachment->fileName);

                $image = $this->conversationService->getAttachmentUrl() . $fileName;
            }

            /**
             * TODO: Optimze to use batch query of senders
             */
            $sender = $userService->findUserById($messageDto->senderId);

            $message = new Message($messageDto->id);
            $message->status = $messageDto->recipientRead ? Message::STATUS_READ : Message::STATUS_DELIVERED;
            $message->userId = $sender ? $sender->id : null;
            $message->chatId = $messageDto->conversationId;
            $message->createdAt = new \DateTime("@" . $messageDto->timeStamp);
            $message->content = [
                "text" => $messageDto->text,
                "image" => $image
            ];

            $out[$id] = $message;
        }

        return $out;
    }


    public function findChatsParticipantIds($chatIds)
    {
        $userService = \BOL_UserService::getInstance();

        $out = [];
        foreach ($chatIds as $id) {
            $conversationDto = $this->conversationService->getConversation($id);

            /**
             * TODO: Optimze to use batch query of participantss
             */
            $initiator = $userService->findUserById($conversationDto->initiatorId);
            $interlocutor = $userService->findUserById($conversationDto->interlocutorId);

            $out[$id] = array_unique([
                $initiator ? $initiator->id : null,
                $interlocutor ? $interlocutor->id : null
            ]);
        }

        return $out;
    }

    public function findChatsMessageIds($chatIds, $args)
    {
        $notReadBy = empty($args["filter"]["notReadBy"]) ? null : $args["filter"]["notReadBy"];

        $out = [];
        foreach ($chatIds as $id) {
            $out[$id] = [];

            $example = new \OW_Example();
            $example->andFieldEqual("conversationId", $id);

            if ($notReadBy) {
                $example->andFieldEqual("recipientId", $notReadBy);
                $example->andFieldEqual("recipientRead", 0);
            }

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
        $notReadBy = empty($args["filter"]["notReadBy"]) ? null : $args["filter"]["notReadBy"];

        $out = [];
        foreach ($chatIds as $id) {
            $out[$id] = [];

            $example = new \OW_Example();
            $example->andFieldEqual("conversationId", $id);

            if ($notReadBy) {
                $example->andFieldEqual("recipientId", $notReadBy);
                $example->andFieldEqual("recipientRead", 0);
            }

            $out[$id] = $this->messageDao->countByExample($example);
        }

        return $out;
    }

    public function addMessage($args)
    {
        $userId = $args["userId"];
        $content = $args["content"];

        $chatId = $args["chatId"];
        $recipientIds = $args["recipientIds"];

        $chat = null;
        if ($chatId) {
            $chat = $this->conversationService->getConversation($chatId);
        } else if ($recipientIds) {
            $chat = $this->conversationService->createChatConversation($userId, $recipientIds[0]);
        }

        $messageDto = $this->conversationService->createMessage($chat, $userId, $content["text"]);

        return $messageDto->id;
    }

    public function markMessagesAsRead($args)
    {
        $userId = $args["userId"];
        $messageIds = $args["messageIds"];

        $this->conversationService->markMessageIdListReadByUser($messageIds, $userId);

        return $messageIds;
    }
}
