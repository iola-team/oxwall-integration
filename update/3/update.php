<?php

/**
 * Update languages
 */
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . "langs.zip", "iola");

/**
 * Add widgets
 */
$widgetService = Updater::getWidgetService();
$widget = $widgetService->addWidget("IOLA_CMP_AppBannerWidget", false);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);
