<?php

namespace Iola\Api\Contract\PushNotifications;

interface NotificationInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getBody();
}