<?php

namespace Iola\Api\Contract\Integration;

use Iola\Api\Entities\Chat;
use Iola\Api\Entities\Message;

interface ChatRepositoryInterface
{
    /**
     * @param string[] $ids
     *
     * @return Chat[]
     */
    public function findChatsByIds($ids);

    /**
     *
     * @param string[] $userIds
     * @param array $args
     * @return string[]
     */
    public function findChatIdsByUserIds($userIds, array $args);

    /**
     *
     * @param string[] $userIds
     * @param array $args
     * @return int[]
     */
    public function countChatsByUserIds($userIds, array $args);


    /**
     * @param string[] $ids
     *
     * @return Message[]
     */
    public function findMessagesByIds($ids);

    /**
     * @param string[] $chatIds
     *
     * @return mixed[]
     */
    public function findChatsParticipantIds($chatIds);

    /**
     * @param string[] $chatIds
     * @param mixed[] $args
     *
     * @return mixed[]
     */
    public function findChatsMessageIds($chatIds, $args);

    /**
     * @param string[] $chatIds
     * @param mixed[] $args
     *
     * @return int[]
     */
    public function countChatsMessages($chatIds, $args);


    /**
     * @param array $args
     *
     * @return mixed
     */
    public function addMessage($args);

    /**
     * @param array $args
     *
     * @return mixed[]
     */
    public function markMessagesAsRead($args);
}
