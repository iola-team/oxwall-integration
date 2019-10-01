<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\FriendshipRepositoryInterface;
use Iola\Api\Entities\Friendship;

class FriendshipRepository implements FriendshipRepositoryInterface
{
    /**
     *
     * @var \FRIENDS_BOL_FriendshipDao
     */
    protected $friendshipDao;

    /**
     *
     * @var \FRIENDS_BOL_Service
     */
    protected $friendsService;

    /**
     *
     * @var \OW_Database
     */
    protected $dbo;

    /**
     *
     * @var \OW_EventManager
     */
    protected $eventManager;

    protected $statuses = [
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING => Friendship::STATUS_PENDING,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE => Friendship::STATUS_ACTIVE,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_IGNORED => Friendship::STATUS_IGNORED
    ];

    public function __construct()
    {
        $this->friendsService = \FRIENDS_BOL_Service::getInstance();
        $this->friendshipDao = \FRIENDS_BOL_FriendshipDao::getInstance();
        $this->dbo = \OW::getDbo();
        $this->eventManager = \OW::getEventManager();
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
        $dtoList = $this->friendshipDao->findByIdList($ids);

        foreach ($dtoList as $friendshipDto) {
            $event = new \OW_Event("friends.cancelled", [
                "senderId" => $friendshipDto->userId,
                "recipientId" => $friendshipDto->friendId
            ]);
    
            $this->eventManager->trigger($event);
        }

        $this->friendshipDao->deleteByIdList($ids);
    }

    /**
     * TODO: Do not use magic strings for friendship phase - find a way to use constants
     *
     * @param string $select
     * @param array $filter
     * @return array
     */
    protected function buildSubQueries($select, array $filter)
    {
        $friendIdIn = $filter["friendIdIn"];
        $phaseIn = $filter["friendshipPhaseIn"];
        $online = $filter["online"];

        $userQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "friendId");
        $friendQueryParts = \BOL_UserDao::getInstance()->getUserQueryFilter("fr", "userId");
        $tableName = $this->friendshipDao->getTableName();

        if ($online === true) {
            $onlineTableName = \BOL_UserOnlineDao::getInstance()->getTableName();
            $onlineJoin = " INNER JOIN `$onlineTableName` AS `online` ON `fr`.`%s` = `online`.`userId` ";

            $userQueryParts["join"] .= sprintf($onlineJoin, "friendId");
            $friendQueryParts["join"] .= sprintf($onlineJoin, "userId");
        }

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
     * @param array $filter
     * @param int $offset
     * @param int $count
     * @return \FRIENDS_BOL_Friendship[]
     */
    protected function findUserFriendshipDtos($userId, array $filter = [], $offset, $count)
    {
        $queries = $this->buildSubQueries("`fr`.*", $filter);
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
     * @param array $filter
     * @return int
     */
    protected function countUserFriendship($userId, array $filter = [])
    {
        $queries = $this->buildSubQueries("COUNT(`fr`.id) as `count`", $filter);
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
                $args["filter"],
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
                $args["filter"]
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

        if ($status === Friendship::STATUS_PENDING) {
            $this->friendsService->onRequest($userId, $friendId);
        }
        
        if ($status === Friendship::STATUS_ACTIVE) {
            $this->friendsService->onAccept($userId, $friendId, $friendshipDto);
        }
        
        return $friendshipDto->id;
    }

    public function updateFriendship($friendshipId, $status)
    {
        $statuses = array_flip($this->statuses);
        $friendshipDto = $this->friendshipDao->findById($friendshipId);

        $friendshipDto->status = $statuses[$status];
        $this->friendshipDao->save($friendshipDto);

        if ($status === Friendship::STATUS_ACTIVE) {
            $this->friendsService->onAccept(
                $friendshipDto->friendId, $friendshipDto->userId, $friendshipDto
            );
        }

        return $friendshipDto->id;
    }

    /**
     * @param int $userId
     * @param int $friendId
     * @return \FRIENDS_BOL_Friendship
     */
    protected function findFriendshipDto($userId, $friendId)
    {
        $query = 
            "SELECT * FROM " . $this->friendshipDao->getTableName() . " WHERE 
                (userId=:userId AND friendId=:friendId) 
                    OR 
                (userId=:friendId AND friendId=:userId)";

        return $this->dbo->queryForObject($query, $this->friendshipDao->getDtoClassName(), [
            "userId" => $userId,
            "friendId" => $friendId
        ]);
    }

    public function findFriendship($userId, $friendId)
    {
        $friendshipDto = $this->findFriendshipDto($userId, $friendId);

        return $friendshipDto ? $this->buildFriendship($friendshipDto) : null;
    }
}
