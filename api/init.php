<?php

namespace Everywhere\Api;

/**
 * @var $app App
 */
use Everywhere\Api\Contract\App\EventManagerInterface;
use Everywhere\Api\Contract\Integration\EventSourceInterface;
use Everywhere\Api\Contract\Integration\IntegrationInterface;

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

