<?php

namespace Iola\Api\Middleware\ServerEvents;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Middleware
{
    protected $responseHeaders = [
        "Cache-Control" => "no-cache",
        "Content-Type" => "text/event-stream"
    ];

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /**
         * Clear output buffer
         */
        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        /**
         * Disable output buffer
         */
        ob_implicit_flush();

        foreach ($this->responseHeaders as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $next($request, $response);
    }
}

