<?php

namespace Everywhere\Oxwall;
use OW;
use OW_EventManager;
use OW_Event;

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
         * Prepare MySQL connection
         */
        OW::getDbo()->query("SET NAMES utf8mb4");

        /**
         * Init iola integration
         */
        App::getInstance()->init();
    });
}

/**
 * Post install fixes
 */
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_INSTALL, function(OW_Event $event) {
    $params = $event->getParams();
    $dbPrefix = OW_DB_PREFIX;
    $sql = [];

    switch ($params["pluginKey"]) {

        /**
         * Mailbox plugin tweaks
         */
        case "mailbox":
            /**
             * Set UTF8 charset to messages content column
             * 
             * TODO: Try to extract this logic somewhere to not repeat it here and in `install.php`
             */
            $sql[] = "ALTER TABLE `{$dbPrefix}mailbox_message` MODIFY `text` mediumtext CHARACTER SET utf8mb4 NOT NULL;";
        break;
    }
    
    foreach ( $sql as $query ) {
        try {
            OW::getDbo()->query($query);
        } catch ( \Exception $e ) {
            // Skip...
        }
    }
});