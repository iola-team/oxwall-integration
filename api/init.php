<?php
/**
 * Copyright © 2019 iola. All rights reserved. Contacts: <hello@iola.app>
 * iola is licensed under a Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License.
 * You should have received a copy of the license along with this work. If not, see <http://creativecommons.org/licenses/by-nc-sa/4.0/>
 */

namespace Iola\Api;

/**
 * @var $app App
 */
use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Integration\EventSourceInterface;
use Iola\Api\Contract\Integration\IntegrationInterface;

$app;

$container = $app->getContainer();

/**
 * @var $integration IntegrationInterface
 */
$integration = $container[IntegrationInterface::class];

/**
 * @var $eventManager EventManagerInterface
 */
$eventManager = $container[EventManagerInterface::class];

/**
 * Listen to all subscription events and add them to shared event source
 */
$eventManager->useListenerProvider($container[EventSourceInterface::class]);

/**
 * Init integration
 */
$integration->init($eventManager);

