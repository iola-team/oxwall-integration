<?php

namespace Iola\Api;

/**
 * @var $app App
 */
use Iola\Api\Contract\App\EventManagerInterface;
use Iola\Api\Contract\Integration\EventSourceInterface;
use Iola\Api\Contract\Integration\IntegrationInterface;
use Iola\Api\Contract\PushNotifications\NotificationManagerInterface;

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
 * Listen for push notification events
 */
$eventManager->useListenerProvider($container[NotificationManagerInterface::class]);

/**
 * Listen to all subscription events and add them to shared event source
 */
$eventManager->useListenerProvider($container[EventSourceInterface::class]);

/**
 * Init integration
 */
$integration->init($eventManager);

