<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api\Controllers;

use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GraphqlController
{
    /**
     * @var StandardServer
     */
    private $server;

    public function __construct(ServerConfig $serverConfigs)
    {
        $this->server = new StandardServer($serverConfigs);
    }

    public function query(ServerRequestInterface $request, ResponseInterface $response, $args)
    {
        return $this->server->processPsrRequest($request, $response, $response->getBody());
    }
}
