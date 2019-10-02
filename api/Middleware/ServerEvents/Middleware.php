<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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

