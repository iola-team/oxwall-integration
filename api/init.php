<?php

namespace Everywhere\Api;

/**
 * @var $app App
 */
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\EventSourceInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;
use Everywhere\Api\Contract\Schema\ViewerInterface;

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
 * @var $viewer ViewerInterface
 */
$viewer = $container[ViewerInterface::class];

/**
 * Init integration
 * 
 * TODO: `$viewer->getUserId()` will always return `null` if called directly in `$integration->init`. Think of how it can be fixed.
 */
$integration->init($eventManager, $viewer);

