<?php

namespace Iola\Api\PushNotifications\Pushers;

use Iola\Api\Contract\PushNotifications\NotificationPusherInterface;

class AppCenterPusher implements NotificationPusherInterface
{
   public function push(array $deviceIds, $notification)
   {
       printVar($notification);
   }
}