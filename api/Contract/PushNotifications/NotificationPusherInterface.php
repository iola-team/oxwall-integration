<?php

namespace Iola\Api\Contract\PushNotifications;

use Iola\Api\Contract\PushNotifications\NotificationInterface;

interface NotificationPusherInterface
{
    /**
     * @param string[] $deviceIds
     * @param NotificationInterface $notification
     * @return void
     */
    public function push(array $deviceIds, $notification);
}