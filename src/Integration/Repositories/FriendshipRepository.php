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
    protected $statuses = [
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_ACTIVE => Friendship::STATUS_ACCEPTED,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_IGNORED => Friendship::STATUS_IGNORED,
        \FRIENDS_BOL_FriendshipDao::VAL_STATUS_PENDING => Friendship::STATUS_PENDING
    ];

    public function __construct()
    {
        $this->friendshipDao = \FRIENDS_BOL_FriendshipDao::getInstance();
    }

    public function findByIds($ids)
    {
        $dtoList = $this->friendshipDao->findByIdList($ids);
        $out = [];

        foreach ($dtoList as $friendshipDto) {
            $friendship = new Friendship();
            $friendship->id = $friendshipDto->id;
            $friendship->userId = $friendshipDto->userId;
            $friendship->friendId = $friendshipDto->friendId;
            $friendship->status = $this->statuses[$friendshipDto->status];
            $friendship->createdAt = new \DateTime("@" . $friendshipDto->timeStamp);

            $out[$friendshipDto->id] = $friendship;
        }

        return $out;
    }

    public function deleteByIds($ids)
    {
        $this->friendshipDao->deleteByIdList($ids);
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
        $example = new \OW_Example();
        $example->andFieldEqual("userId", $userId);
        $example->andFieldEqual("friendId", $friendId);

        $id = $this->friendshipDao->findIdByExample($example);

        if (!$id) {
            return $this->findFriendshipId($friendId, $userId);
        }

        return $id;
    }
}
