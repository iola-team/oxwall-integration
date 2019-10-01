<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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