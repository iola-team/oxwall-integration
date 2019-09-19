<?php

namespace Iola\Api;

return [
    "displayErrorDetails" => true,
    "schema" => require __DIR__ . '/schema.php',
    "notifications" => require __DIR__ . '/notifications.php',
    "jwt" => [
        "secret" => "iola",
        "lifeTime" => 60 * 60 * 24 * 30 * 12 // One year
    ]
];