<?php

namespace Everywhere\Api;

return [
    "displayErrorDetails" => true,
    "schema" => require __DIR__ . '/schema.php',
    "jwt" => [
        "secret" => "qqreq",
        "lifeTime" => 6000
    ]
];