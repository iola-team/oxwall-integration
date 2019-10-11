<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

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