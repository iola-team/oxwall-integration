<?php

namespace Everywhere\Oxwall;
use OW;
use OW_EventManager;

/**
 * Apply init patches
 */
require_once __DIR__ . "/patches/init.php";

$requiredPlugins = [
    "mailbox", "friends"
];

$isReady = true;
foreach ($requiredPlugins as $pluginKey) {
    if (!OW::getPluginManager()->isPluginActive($pluginKey)) {
        $isReady = false;

        continue;
    }
}

/**
 * Init the plugin if all the requirements are met
 */
if ($isReady) {
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
        OW::getRequestHandler()->addCatchAllRequestsExclude($exceptionKey, RootController::class);
    }

    /**
     * Routes
     */
    $rootRoute = new RootRoute("everywhere-api", "everywhere/api");
    OW::getRouter()->addRoute($rootRoute);

    /**
     * Init Application
     */
    OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, function() {

        /**
         * Init iola integration
         */
        App::getInstance()->init();
    });
}