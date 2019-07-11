<?php

namespace Iola\Oxwall;

use Iola\Api\Server as IolaServer;
use Iola\Api\Contract\ServerInterface;
use Iola\Oxwall\Integration;
use OW;
use OW_Singleton;

/**
 * Singleton decorator for Graphql Server class
 *
 * @method static Server getInstance()
 */
class Server implements ServerInterface
{
    use OW_Singleton;

    protected $server;

    protected function __construct()
    {
        $baseUrl = OW::getRouter()->urlForRoute(ServerRoute::ROUTE_NAME);
        $this->server = new IolaServer($baseUrl, new Integration());
    }

    public function init()
    {
        $this->server->init();
    }

    public function run($path)
    {
        $this->server->run($path);
    }


}
