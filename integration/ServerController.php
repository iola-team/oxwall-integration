<?php

namespace Iola\Oxwall;

class ServerController extends \OW_ActionController
{
    public function index($params) {
        Server::getInstance()->run($params[ServerRoute::PATH_ATTR]);

        exit;
    }
}
