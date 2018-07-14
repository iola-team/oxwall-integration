<?php

namespace Everywhere\Oxwall;

use Everywhere\Api\Server;
use Everywhere\Api\Contract\ServerInterface;
use Everywhere\Oxwall\Integration\Integration;

/**
 * Singleton decorator for Graphql Server class
 *
 * @method static App getInstance()
 */
class App implements ServerInterface
{
    use \OW_Singleton;

    protected $server;

    protected function __construct()
    {
        $baseUrl = \OW::getRouter()->urlForRoute("everywhere-api");
        $this->server = new Server($baseUrl, new Integration());
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
