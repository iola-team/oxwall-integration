<?php

namespace Iola\Api\Contract\Integration;

use Iola\Api\Entities\Friendship;

interface FriendshipRepositoryInterface
{
    /**
     *
     * @param string[] $ids
     * @return Friendship[]
     */
    public function findByIds($ids);

    /**
     *
     * @param string[] $ids
     * @return void
     */
    public function deleteByIds($ids);

    /**
     *
     * @param string[] $userIds
     * @param array $args
     * @return Friendship[]
     */
    public function findByUserIds($userIds, array $args);

    /**
     *
     * @param string[] $userIds
     * @param array $args
     * @return string[]
     */
    public function countByUserIds($userIds, array $args);

    /**
     *
     * @param string $userId
     * @param string $friendId
     * @param string $status
     * @return string
     */
    public function createFriendship($userId, $friendId, $status);

    /**
     *
     * @param string $friendshipId
     * @param string $status
     * @return string
     */
    public function updateFriendship($friendshipId, $status);

    /**
     *
     * @param string $userId
     * @param string $friendId
     * @return Friendship|null
     */
    public function findFriendship($userId, $friendId);
}