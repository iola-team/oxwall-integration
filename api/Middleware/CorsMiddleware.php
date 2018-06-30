<?php

namespace Everywhere\Api\Middleware;

use Tuupola\Middleware\Cors;

class CorsMiddleware extends Cors
{
    public function __construct($options = [])
    {
        $resultOptions = array_merge([
            "headers.allow" => ["Content-Type", "Cache-Control", "Connection"],
        ], $options);

        parent::__construct($resultOptions);
    }
}
