<?php

namespace Iola\Api\PushNotifications;

use Iola\Api\Contract\PushNotifications\NotificationInterface;

class Notification implements NotificationInterface
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $body;

    public function __construct($body, $title = null)
    {
        $this->body = $body;
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getBody()
    {
        return $this->body;
    }
}