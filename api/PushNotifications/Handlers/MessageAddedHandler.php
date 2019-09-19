<?php

namespace Iola\Api\PushNotifications\Handlers;

use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Contract\PushNotifications\NotificationHandlerInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Entities\Message;
use Iola\Api\Integration\Events\MessageAddedEvent;
use Iola\Api\PushNotifications\Notification;

class MessageAddedHandler implements NotificationHandlerInterface
{
    /**
     * @var DataLoaderInterface
     */
    private $messageLoader;

    /**
     * @var DataLoaderInterface
     */
    private $chatParticipansLoader;

    public function __construct(
        ChatRepositoryInterface $chatRepository,
        DataLoaderFactoryInterface $loaderFactory
    ) {
        $this->messageLoader = $loaderFactory->create(function($ids) use ($chatRepository) {
            return $chatRepository->findMessagesByIds($ids);
        });

        $this->chatParticipansLoader = $loaderFactory->create(function($ids) use ($chatRepository) {
            return $chatRepository->findChatsParticipantIds($ids);
        });
    }

    public function shouldHandleEvent($event)
    {
        return $event instanceof MessageAddedEvent;
    }

    /**
     * @param MessageAddedEvent $event
     */
    public function getTargetUserIds($event)
    {
        $messageId = $event->getMessageId();

        return $this->messageLoader->load($messageId)->then(function(Message $message) {
            return $this->chatParticipansLoader->load($message->chatId);
        });
    }

    /**
     * @param string|int $userId
     * @param MessageAddedEvent $event
     */
    public function createNotification($userId, $event)
    {
        $messageId = $event->getMessageId();
        
        return $this->messageLoader->load($messageId)->then(function(Message $message) {
            return new Notification();
        });
    }
}