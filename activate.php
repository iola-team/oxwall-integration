<?php

/**
 * Add widgets
 */
$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('IOLA_CMP_AppBannerWidget', false);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_INDEX);