<?php

use \Everywhere\Oxwall\App;

require_once __DIR__ . "/vendor/autoload.php";

$rootRoute = new Everywhere\Oxwall\RootRoute("everywhere-api", "everywhere/api");
OW::getRouter()->addRoute($rootRoute);

App::getInstance()->init();
