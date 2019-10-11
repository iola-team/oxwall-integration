<?php

namespace Iola\Api\Contract\Integration;

interface BlockRepositoryInterface
{
    /**
     *
     * @param string[] $userIds
     * @param array $args
     * @return string[]
     */
    public function findByUserIds($userIds, array $args);

    /**
     *
     * @param string[] $userIds
     * @param array $args
     * @return int[]
     */
    public function countByUserIds($userIds, array $args);

    /**
     * @param string[] $userIds
     * @param string $byUserId
     * @return boolean[]
     */
    public function isBlockedByUser($userIds, $byUserId);

    /**
     * @param string[] $userIds
     * @param string $blockUserId
     * @return boolean[]
     */
    public function hasBlockedUser($userIds, $blockUserId);

    /**
     * @param string|int $userId
     * @param string|int $blockUserId
     * @return boolean
     */
    public function blockUser($userId, $blockUserId);

    /**
     * @param string|int $userId
     * @param string|int $blockedUserId
     * @return boolean
     */
    public function unblockUser($userId, $blockedUserId);
}