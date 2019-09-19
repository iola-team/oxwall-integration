<?php

use Iola\Api\PushNotifications\Pushers;
use Iola\Api\PushNotifications\Handlers;

return [
    "pushers" => [
        Pushers\AppCenterPusher::class
    ],
    "handlers" => [
        Handlers\MessageAddedHandler::class
    ]
];