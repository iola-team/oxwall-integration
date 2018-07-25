<?php

namespace Everywhere\Oxwall;

/**
 * Apply patches to Oxwall classes
 */
require_once __DIR__ . "/patches/patch.php";
require_once __DIR__ . "/vendor/autoload.php";

$extensionManager = new ExtensionManager();
$extensionManager->init();

$rootRoute = new RootRoute("everywhere-api", "everywhere/api");
\OW::getRouter()->addRoute($rootRoute);

App::getInstance()->init();
