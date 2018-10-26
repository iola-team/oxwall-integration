<?php

namespace Everywhere\Api\Controllers;

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
