<?php

use Iola\Oxwall\ServerRoute;

require_once __DIR__ . "/vendor/autoload.php";

/**
 * Apply init patches
 */
require_once __DIR__ . "/patches/init.php";

/**
 * Admin routes
 */
OW::getRouter()->addRoute(
    new OW_Route("iola.admin-settings", "admin/plugins/iola", "IOLA_CTRL_Admin", "index")
);

OW::getRouter()->addRoute(
    new OW_Route("iola.admin-settings-save", "admin/plugins/iola/save-settings", "IOLA_CTRL_Admin", "saveSettings")
);

/**
 * Check if all requirements are met
 */
if (IOLA_CLASS_Plugin::getInstance()->isReady()) {
    /**
     * API route
     */
    OW::getRouter()->addRoute(new ServerRoute("iola/api"));
}

IOLA_CLASS_Plugin::getInstance()->init();