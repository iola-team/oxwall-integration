<?php

$patchedPlugins = require __DIR__ . "/plugins.php";

foreach ($patchedPlugins as $pluginKey) {
    $pluginDto = BOL_PluginService::getInstance()->findPluginByKey($pluginKey);
    $installPatchPath = __DIR__ . "/$pluginKey/install.php";

    if ($pluginDto !== null && file_exists($installPatchPath)) {
        require $installPatchPath;
    }
}