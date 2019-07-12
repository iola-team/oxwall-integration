<?php

use Iola\Oxwall\ServerController;
use Iola\Oxwall\ServerRoute;
use Iola\Oxwall\Server;

require_once __DIR__ . "/vendor/autoload.php";

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
 * Admin routes
 */
OW::getRouter()->addRoute(
    new OW_Route("iola.admin-settings", "admin/plugins/iola", "IOLA_CTRL_Admin", "index")
);

/**
 * Init the plugin if all the requirements are met
 */
if ($isReady) {
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
        OW::getRequestHandler()->addCatchAllRequestsExclude($exceptionKey, ServerController::class);
    }

    /**
     * API route
     */
    OW::getRouter()->addRoute(new ServerRoute("everywhere/api"));

    /**
     * Init Application
     */
    OW::getEventManager()->bind(OW_EventManager::ON_PLUGINS_INIT, function() {

        /**
         * Init iola integration
         */
        Server::getInstance()->init();
    });
}