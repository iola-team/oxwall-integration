<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Entities\Friendship;

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
     * @return string|null
     */
    public function findFriendshipId($userId, $friendId);
}