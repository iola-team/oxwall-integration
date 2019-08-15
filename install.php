<?php

require_once __DIR__ . "/classes/plugin.php";

$iolaPlugin = IOLA_CLASS_Plugin::getInstance()->getPlugin();
$dbPrefix = OW_DB_PREFIX;

$sql = [
    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}iola_subscription_event` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `timeOffset` bigint(20) NULL,
      `data` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `timeOffset` (`timeOffset`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;",

    "CREATE TABLE IF NOT EXISTS `{$dbPrefix}iola_subscription` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `streamId` varchar(255) NOT NULL,
      `query` text NOT NULL,
      `variables` text NOT NULL,
      PRIMARY KEY (`id`),
      KEY `streamId` (`streamId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
];

foreach ( $sql as $query ) {
    try {
        OW::getDbo()->query($query);
    } catch ( Exception $e ) {
        // Skip...
    }
}

/**
 * Configure the plugin settings page
 */
OW::getPluginManager()->addPluginSettingsRouteName($iolaPlugin->getKey(), "iola.admin-settings");

/**
 * Import language dump
 */
try {
    BOL_LanguageService::getInstance()->importPrefixFromZip(
        __DIR__ . "/langs.zip",
        $iolaPlugin->getKey()
    );
} catch ( Exception $e ) {
    // Skip...
}

/**
 * Apply install patches
 */
require_once __DIR__ . "/patches/install.php";

/**
 * The code below makes sure that the plugin will always init before other non-system plugins
 */
$firstNonSystemPluginId = OW::getDbo()->queryForColumn(
    "SELECT id FROM `{$dbPrefix}base_plugin` WHERE `isSystem`=0 ORDER BY `id` LIMIT 1"
);
$maxPluginId = OW::getDbo()->queryForColumn(
    "SELECT MAX(`id`) FROM `{$dbPrefix}base_plugin`"
);

OW::getDbo()->update(
    "UPDATE `{$dbPrefix}base_plugin` SET `id`=:toId WHERE `id`=:fromId",
    [
        "toId" => $maxPluginId + 1,
        "fromId" => $firstNonSystemPluginId
    ]
);

OW::getDbo()->update(
    "UPDATE `{$dbPrefix}base_plugin` SET `id`=:toId WHERE `id`=:fromId",
    [
        "toId" => $firstNonSystemPluginId,
        "fromId" => $iolaPlugin->getDto()->getId()
    ]
);
