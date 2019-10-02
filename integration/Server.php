<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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
