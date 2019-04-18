<?php

namespace Everywhere\Api;

return [
    "displayErrorDetails" => true,
    "schema" => require __DIR__ . '/schema.php',
    "jwt" => [
        "secret" => "iola",
        "lifeTime" => 60 * 60 * 24 * 30 * 12 // One year
    ]
];