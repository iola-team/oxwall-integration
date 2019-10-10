<?php

namespace Iola\Oxwall\Repositories;

use Iola\Api\Contract\Integration\BlockRepositoryInterface;
use OW;
use OW_Event;
use OW_EventManager;
use BOL_UserBlock;
use BOL_UserBlockDao;
use BOL_UserService;

class BlockRepository implements BlockRepositoryInterface
{
    /**
     * @var BOL_UserService
     */
    private $userService;

    /**
     * @var BOL_UserBlockDao
     */
    private $userBlockDao;

    public function __construct()
    {
        $this->userService = BOL_UserService::getInstance();
        $this->userBlockDao = BOL_UserBlockDao::getInstance();
    }

    public function findByUserIds($userIds, array $args)
    {
        $offset = $args["offset"];
        $count = $args["count"];

        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = $this->userService->findBlockedUserList($userId, $offset, $count);
        }
        
        return $out;
    }

    public function countByUserIds($userIds, array $args)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = $this->userService->countBlockedUsers($userId);
        }
        
        return $out;
    }

    public function isBlockedByUser($userIds, $byUserId)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = $this->userService->isBlocked($userId, $byUserId);
        }
        
        return $out;
    }

    public function hasBlockedUser($userIds, $blockUserId)
    {
        $out = [];
        foreach ($userIds as $userId) {
            $out[$userId] = $this->userService->isBlocked($blockUserId, $userId);
        }
        
        return $out;
    }

    public function blockUser($userId, $blockUserId)
    {
        $isBlocked = $this->userService->isBlocked($blockUserId, $userId);
        
        if ($isBlocked) {
            return;
        }

        $dto = new BOL_UserBlock();

        $dto->userId = $userId;
        $dto->blockedUserId = $blockUserId;
        $this->userBlockDao->save($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_BLOCK, [
            'userId' => $userId,
            'blockedUserId' => $blockUserId
        ]);

        OW::getEventManager()->trigger($event);
    }

    public function unblockUser($userId, $blockedUserId)
    {
        $dto = $this->userBlockDao->findBlockedUser($userId, $blockedUserId);
        $this->userBlockDao->delete($dto);

        $event = new OW_Event(OW_EventManager::ON_USER_UNBLOCK, [
            'userId' => $userId,
            'blockedUserId' => $blockedUserId
        ]);

        OW::getEventManager()->trigger($event);
    }
}
