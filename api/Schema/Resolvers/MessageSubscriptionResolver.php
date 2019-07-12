<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\ChatRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Contract\Schema\SubscriptionFactoryInterface;
use Iola\Api\Entities\Message;
use Iola\Api\Integration\Events\MessageAddedEvent;
use Iola\Api\Integration\Events\MessageUpdatedEvent;
use Iola\Api\Schema\IDObject;
use Iola\Api\Schema\SubscriptionResolver;
use Iola\Api\Contract\Schema\Relay\EdgeFactoryInterface;

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

    /**
     * @var EdgeFactoryInterface
     */
    protected $edgeFactory;

    public function __construct(
        ChatRepositoryInterface $chatRepository,
        DataLoaderFactoryInterface $loaderFactory,
        SubscriptionFactoryInterface $subscriptionFactory,
        EdgeFactoryInterface $edgeFactory
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

        $this->edgeFactory = $edgeFactory;
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
        $userId = empty($args["userId"]) ? null : $args["userId"];

        return [
            "node" => $message,
            "user" => $userId,
            "chat" => $message->chatId,
            "chatEdge" => function($root, $arguments) use($userId, $message) {
                return $this->edgeFactory->createFromArguments($arguments, [
                    "node" => $message->chatId,
                    "userId" => $userId
                ]);
            },
            "edge" => function($root, $arguments) use($message) {
                return $this->edgeFactory->createFromArguments($arguments, $message);
            }
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
