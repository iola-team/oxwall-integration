<?php

namespace Iola\Api\Contract\PushNotifications;

use GraphQL\Executor\Promise\Promise;
use Iola\Api\Contract\App\EventInterface;
use Iola\Api\Contract\PushNotifications\NotificationInterface;

interface NotificationHandlerInterface
{
    /**
     * @param EventInterface $event
     * @return boolean|Promise
     */
    public function shouldHandleEvent($event);

    /**
     * @param EventInterface $event
     * @return string[]|int[]|Promise
     */
    public function getTargetUserIds($event);

    /**
     * @param string|int $userId
     * @param EventInterface $event
     * @return NotificationInterface|Promise
     */
    public function createNotification($userId, $event);
}