<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\ChatRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Contract\Schema\SubscriptionInterface;
use Everywhere\Api\Entities\Message;
use Everywhere\Api\Integration\Events\MessageAddedEvent;
use Everywhere\Api\Integration\Events\MessageUpdatedEvent;
use Everywhere\Api\Schema\IDObject;
use Everywhere\Api\Schema\SubscriptionResolver;

class MessageSubscriptionResolver extends SubscriptionResolver
{
    /**
     * @var SubscriptionFactoryInterface
     */
    protected $subscriptionFactory;

    /**
     * @var DataLoaderInterface
     */
    protected $messageLoader;

    /**
     * @var ChatRepositoryInterface
     */
    protected $chatRepository;

    public function __construct(
        ChatRepositoryInterface $chatRepository,
        DataLoaderFactoryInterface $loaderFactory,
        SubscriptionFactoryInterface $subscriptionFactory
    )
    {
        parent::__construct([
            "onMessageAdd" => function($root, $args) {
                return $this->createSubscription($args, MessageAddedEvent::EVENT_NAME);
            },

            "onMessageUpdate" => function($root, $args) {
                return $this->createSubscription($args, MessageUpdatedEvent::EVENT_NAME);
            }
        ]);

        $this->messageLoader = $loaderFactory->create(function($ids) use($chatRepository) {
            return $chatRepository->findMessagesByIds($ids);
        });

        $this->subscriptionFactory = $subscriptionFactory;
        $this->chatRepository = $chatRepository;
    }

    protected function createSubscription(array $args, $eventName)
    {
        return $this->subscriptionFactory->create(
            $eventName,
            function ($data) use ($args) {
                return $this->loadMessage($data)->then(function($message) use($args) {
                    return $this->filterEvents($message, $args);
                });
            },

            function ($data) use($args) {
                return $this->loadMessage($data)->then(function($message) use($args) {
                    return $this->createMessagePayload($message, $args);
                });
            }
        );
    }

    protected function loadMessage($data)
    {
        return $this->messageLoader->load($data["messageId"]);
    }

    protected function createMessagePayload(Message $message, array $args)
    {
        return [
            "node" => $message,
            "user" => $message->userId,
            "chat" => $message->chatId,
            "chatEdge" => [
                "cursor" => "tmp-cursor", // TODO: use real cursor
                "node" => $message->chatId
            ],
            "edge" => [
                "cursor" => "tmp-cursor", // TODO: use real cursor
                "node" => $message
            ]
        ];
    }

    protected function filterEvents(Message $message, array $args)
    {
        /**
         * @var $userIdObject IDObject
         */
        $userIdObject = empty($args["userId"]) ? null : $args["userId"];

        /**
         * @var $chatIdObject IDObject
         */
        $chatIdObject = empty($args["chatId"]) ? null : $args["chatId"];

        if ($chatIdObject && $message->chatId == $chatIdObject->getId()) {
            return true;
        }

        if (!$userIdObject) {
            return false;
        }

        $participantIds = $this->chatRepository->findChatsParticipantIds([$message->chatId])[$message->chatId];

        return in_array($userIdObject->getId(), $participantIds);
    }
}
