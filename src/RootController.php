<?php

namespace Everywhere\Oxwall;

class RootController extends \OW_ActionController
{
    public function __construct()
    {

    }

    public function index($params) {
        App::getInstance()->run($params[RootRoute::PATH_ATTR]);

        exit;
    }
}
