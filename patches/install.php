<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

$patchedPlugins = require __DIR__ . "/plugins.php";

foreach ($patchedPlugins as $pluginKey) {
    $pluginDto = BOL_PluginService::getInstance()->findPluginByKey($pluginKey);
    $installPatchPath = __DIR__ . "/$pluginKey/install.php";

    if ($pluginDto !== null && file_exists($installPatchPath)) {
        require $installPatchPath;
    }
}