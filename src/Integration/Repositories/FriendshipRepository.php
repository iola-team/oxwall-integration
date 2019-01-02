<?php

namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\FriendshipRepositoryInterface;
use Everywhere\Api\Entities\Friendship;

class FriendshipRepository implements FriendshipRepositoryInterface
{
    /**
     *
     * @var \FRIENDS_BOL_FriendshipDao
     */
    protected $friendshipDao;

    /**
     *
     * @var \OW_Database
     */
    protected $dbo;

    protected $statuses = [
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE => Friendship::STATUS_ACTIVE,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_IGNORED => Friendship::STATUS_IGNORED,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING => Friendship::STATUS_PENDING
    ];

    public function __construct()
    {
        $this->friendshipDao = \FRIENDS_BOL_FriendshipDao::getInstance();
        $this->dbo = \OW::getDbo();
    }

    protected function buildFriendship(\FRIENDS_BOL_Friendship $friendshipDto)
    {
        $friendship = new Friendship();
        $friendship->id = $friendshipDto->id;
        $friendship->userId = $friendshipDto->userId;
        $friendship->friendId = $friendshipDto->friendId;
        $friendship->status = $this->statuses[$friendshipDto->status];
        $friendship->createdAt = new \DateTime("@" . $friendshipDto->timeStamp);

        return $friendship;
    }

    public function findByIds($ids)
    {
        $dtoList = $this->friendshipDao->findByIdList($ids);
        $out = array_fill_keys($ids, null);

        foreach ($dtoList as $friendshipDto) {
            $out[$friendshipDto->id] = $this->buildFriendship($friendshipDto);
        }

        return $out;
    }

    public function deleteByIds($ids)
    {
        $this->friendshipDao->deleteByIdList($ids);
    }

    /**
     * Queries user friendship DTO list by userId and status
     * TODO: Try to find a way to reuse existing methods instead of writing low-level queries
     *
     * @param int $userId
     * @param string $status
     * @param int $offset
     * @param int $count
     * @return \FRIENDS_BOL_Friendship[]
     */
    protected function findUserFriendshipDtos($userId, $status, $offset, $count)
    {
        $userQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "friendId");
        $friendQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "userId");
        $tableName = $this->friendshipDao->getTableName();
        $statuses = array_flip($this->statuses);

        $queries = [];

        $queries[] = 
            "SELECT `fr`.* FROM `$tableName` AS `fr` " . $friendQueryParts["join"] . "
                WHERE " . $friendQueryParts["where"] . "
                    AND fr.friendId=:userId
                    AND fr.status=:status";

        $queries[] = 
            "SELECT `fr`.* FROM `$tableName` AS `fr` " . $userQueryParts["join"] . "
                WHERE " . $userQueryParts["where"] . "
                    AND fr.userId=:userId
                    AND fr.status=:status";

        $fullQuery = "(" . implode(") UNION (", $queries) . ") LIMIT :offset, :count";

        $dtoList = $this->dbo->queryForObjectList(
            $fullQuery,
            $this->friendshipDao->getDtoClassName(),
            [
                "userId" => $userId,
                "offset" => $offset,
                "count" => $count,
                "status" => $statuses[$status]
            ]
        );

        $out = [];
        foreach ($dtoList as $friendshipDto) {
            $out[] = $this->buildFriendship($friendshipDto);
        }

        return $out;
    }

    /**
     * Queries user friendship count by userId and status
     * TODO: Try to find a way to reuse existing methods instead of writing low-level queries
     *
     * @param int $userId
     * @param string $status
     * @return int
     */
    protected function countUserFriendship($userId, $status)
    {
        $userQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "friendId");
        $friendQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "userId");
        $tableName = $this->friendshipDao->getTableName();
        $statuses = array_flip($this->statuses);

        $queries = [];

        $queries[] = 
            "SELECT COUNT(`fr`.id) as `count` FROM `$tableName` AS `fr` " . $friendQueryParts["join"] . "
                WHERE " . $friendQueryParts["where"] . "
                    AND fr.friendId=:userId
                    AND fr.status=:status";

        $queries[] = 
            "SELECT COUNT(`fr`.id) FROM `$tableName` AS `fr` " . $userQueryParts["join"] . "
                WHERE " . $userQueryParts["where"] . "
                    AND fr.userId=:userId
                    AND fr.status=:status";

        $fullQuery = "SELECT SUM(`count`) FROM ((" . implode(") UNION (", $queries) . ")) AS unionQuery";

        return $this->dbo->queryForColumn(
            $fullQuery,
            [
                "userId" => $userId,
                "status" => $statuses[$status]
            ]
        );
    }

    public function findByUserIds($userIds, array $args)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = $this->findUserFriendshipDtos(
                $userId,
                $args["filter"]["friendshipStatus"],
                $args["offset"], 
                $args["count"]
            );
        }

        return $out;
    }

    public function countByUserIds($userIds, array $args)
    {
        $out = array_fill_keys($userIds, 0);

        foreach ($userIds as $userId) {
            $out[$userId] = $this->countUserFriendship(
                $userId, 
                $args["filter"]["friendshipStatus"]
            );
        }

        return $out;
    }

    public function createFriendship($userId, $friendId, $status)
    {
        $statuses = array_flip($this->statuses);

        $friendshipDto = new \FRIENDS_BOL_Friendship;
        $friendshipDto->userId = $userId;
        $friendshipDto->friendId = $friendId;
        $friendshipDto->status = $statuses[$status];
        $friendshipDto->timeStamp = time();

        $this->friendshipDao->save($friendshipDto);

        return $friendshipDto->id;
    }

    public function updateFriendship($friendshipId, $status)
    {
        $statuses = array_flip($this->statuses);

        $friendshipDto = $this->friendshipDao->findById($friendshipId);
        $friendshipDto->status = $statuses[$status];

        $this->friendshipDao->save($friendshipDto);

        return $friendshipDto->id;
    }

    public function findFriendshipId($userId, $friendId)
    {
        $query = 
            "SELECT id FROM " . $this->friendshipDao->getTableName() . " WHERE 
                (userId=:userId AND friendId=:friendId) 
                    OR 
                (userId=:friendId AND friendId=:userId)";

        return $this->dbo->queryForColumn($query, [
            "userId" => $userId,
            "friendId" => $friendId
        ]);
    }
}
