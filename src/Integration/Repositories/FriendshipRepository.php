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
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING => Friendship::STATUS_PENDING,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE => Friendship::STATUS_ACTIVE,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_IGNORED => Friendship::STATUS_IGNORED
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
     * TODO: Do not use magic strings for friendship phase - find a way to use constants
     *
     * @param [type] $select
     * @param array $phaseIn
     * @param array $friendIdIn
     * @return void
     */
    protected function buildSubQueries($select, array $phaseIn, array $friendIdIn)
    {
        $userQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "friendId");
        $friendQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "userId");
        $tableName = $this->friendshipDao->getTableName();

        $friendIdInSql = empty($friendIdIn) ? null : $this->dbo->mergeInClause($friendIdIn);
        $pahseToStatus = [
            "REQUEST_RECEIVED" => \FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING,
            "REQUEST_SENT" => \FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING,
            "ACTIVE" => \FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE
        ];

        $phaseIn = empty($phaseIn) ? array_keys($pahseToStatus) : $phaseIn;
        $queries = [];
        $queryPhases = array_intersect(["ACTIVE", "REQUEST_RECEIVED"], $phaseIn);
        if ($queryPhases) {
            $statusIn = array_intersect_key($pahseToStatus, array_flip($queryPhases));
            $statusesInSql = $this->dbo->mergeInClause(array_unique(array_values($statusIn)));

            $queries[] = 
                "SELECT $select FROM `$tableName` AS `fr` " . $friendQueryParts["join"] . "
                    WHERE " . $friendQueryParts["where"] . "
                        AND fr.friendId=:userId AND fr.status IN ($statusesInSql)" .
                        ( $friendIdInSql ? " AND fr.userId IN ($friendIdInSql)" : "" );
        }

        $queryPhases = array_intersect(["ACTIVE", "REQUEST_SENT"], $phaseIn);
        if ($queryPhases) {
            $statusIn = array_intersect_key($pahseToStatus, array_flip($queryPhases));
            $statusesInSql = $this->dbo->mergeInClause(array_unique(array_values($statusIn)));

            $queries[] = 
                "SELECT $select FROM `$tableName` AS `fr` " . $userQueryParts["join"] . "
                    WHERE " . $userQueryParts["where"] . "
                        AND fr.userId=:userId AND fr.status IN ($statusesInSql)" .
                        ( $friendIdInSql ? " AND fr.friendId IN ($friendIdInSql)" : "" );
        }

        return $queries;
    }

    /**
     * Queries user friendship DTO list by userId and status
     * TODO: Try to find a way to reuse existing methods instead of writing low-level queries
     *
     * @param int $userId
     * @param string[] $phaseIn
     * @param string[] $friendIdIn
     * @param int $offset
     * @param int $count
     * @return \FRIENDS_BOL_Friendship[]
     */
    protected function findUserFriendshipDtos($userId, array $phaseIn, array $friendIdIn, $offset, $count)
    {
        $queries = $this->buildSubQueries("`fr`.*", $phaseIn, $friendIdIn);
        $statusesOrder = $this->dbo->mergeInClause(array_keys($this->statuses));

        $fullQuery = 
            "(" . implode(") UNION (", $queries) . ") 
                ORDER BY FIELD(`status`, $statusesOrder)
                LIMIT :offset, :count
            ";

        $dtoList = $this->dbo->queryForObjectList(
            $fullQuery,
            $this->friendshipDao->getDtoClassName(),
            [
                "userId" => $userId,
                "offset" => $offset,
                "count" => $count
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
     * @param string[] $phaseIn
     * @param string[] $friendIdIn
     * @return int
     */
    protected function countUserFriendship($userId, array $phaseIn, array $friendIdIn)
    {
        $queries = $this->buildSubQueries("COUNT(`fr`.id) as `count`", $phaseIn, $friendIdIn);
        $fullQuery = "SELECT SUM(`count`) FROM ((" . implode(") UNION ALL (", $queries) . ")) AS unionQuery";

        return $this->dbo->queryForColumn(
            $fullQuery,
            [
                "userId" => $userId
            ]
        );
    }

    public function findByUserIds($userIds, array $args)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = $this->findUserFriendshipDtos(
                $userId,
                $args["filter"]["friendshipPhaseIn"],
                $args["filter"]["friendIdIn"],
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
                $args["filter"]["friendshipPhaseIn"],
                $args["filter"]["friendIdIn"]
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

    public function findFriendship($userId, $friendId)
    {
        $query = 
            "SELECT * FROM " . $this->friendshipDao->getTableName() . " WHERE 
                (userId=:userId AND friendId=:friendId) 
                    OR 
                (userId=:friendId AND friendId=:userId)";

        $friendshipDto = $this->dbo->queryForObject($query, $this->friendshipDao->getDtoClassName(), [
            "userId" => $userId,
            "friendId" => $friendId
        ]);

        return $friendshipDto ? $this->buildFriendship($friendshipDto) : null;
    }
}
