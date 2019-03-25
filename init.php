<?php

namespace Everywhere\Oxwall;

/**
 * Apply patches to Oxwall classes
 */
require_once __DIR__ . "/patches/patch.php";
require_once __DIR__ . "/vendor/autoload.php";

/**
 * Oxwall classes extension
 */
$extensionManager = new ExtensionManager([]);
$extensionManager->init();

/**
 * Redirect exceptions
 */
$exceptionsKeys = [
    "base.members_only",
    "base.splash_screen",
    "base.password_protected",
    "base.maintenance_mode",
    "base.wait_for_approval",
    "base.suspended_user",
    "base.email_verify",
    "base.complete_profile",
    "base.complete_profile.account_type"
];

foreach ($exceptionsKeys as $exceptionKey) {
    \OW::getRequestHandler()->addCatchAllRequestsExclude($exceptionKey, RootController::class);
}

/**
 * Routes
 */
$rootRoute = new RootRoute("everywhere-api", "everywhere/api");
\OW::getRouter()->addRoute($rootRoute);

App::getInstance()->init();
