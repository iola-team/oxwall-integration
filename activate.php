<?php

/**
 * Add widgets
 */
$widgetService = BOL_ComponentAdminService::getInstance();
$widget = $widgetService->addWidget('IOLA_CMP_AppBannerWidget', false);
$widgetPlace = $widgetService->addWidgetToPlace($widget, BOL_ComponentService::PLACE_DASHBOARD);
$widgetService->addWidgetToPosition($widgetPlace, BOL_ComponentService::SECTION_RIGHT);