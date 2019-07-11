<?php

namespace Iola\Api\Schema\Resolvers;

use Iola\Api\Contract\Integration\PhotoRepositoryInterface;
use Iola\Api\Contract\Integration\CommentRepositoryInterface;
use Iola\Api\Contract\Schema\DataLoaderFactoryInterface;
use Iola\Api\Contract\Schema\DataLoaderInterface;
use Iola\Api\Contract\Schema\SubscriptionFactoryInterface;
use Iola\Api\Entities\Comment;
use Iola\Api\Integration\Events\CommentAddedEvent;
use Iola\Api\Schema\IDObject;
use Iola\Api\Schema\SubscriptionResolver;

class PhotoCommentSubscriptionResolver extends SubscriptionResolver
{
    /**
     * @var PhotoRepositoryInterface
     */
    protected $photoRepository;

    /**
     * @var CommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * @var SubscriptionFactoryInterface
     */
    protected $subscriptionFactory;

    /**
     * @var DataLoaderInterface
     */
    protected $commentLoader;

    public function __construct(
        PhotoRepositoryInterface $photoRepository,
        CommentRepositoryInterface $commentRepository,
        SubscriptionFactoryInterface $subscriptionFactory,
        DataLoaderFactoryInterface $loaderFactory
    )
    {
        parent::__construct([
            "onPhotoCommentAdd" => function($root, $args) {
                return $this->createSubscription($args, CommentAddedEvent::EVENT_NAME);
            },
        ]);

        $this->photoRepository = $photoRepository;
        $this->commentRepository = $commentRepository;
        $this->subscriptionFactory = $subscriptionFactory;

        $this->commentLoader = $loaderFactory->create(function($ids) use($commentRepository) {
            return $commentRepository->findByIds($ids);
        });
    }

    protected function createSubscription(array $args, $eventName)
    {
        return $this->subscriptionFactory->create(
            $eventName,
            function ($data) use ($args) {
                return $this->loadComment($data)->then(function($comment) use($args) {
                    return $this->filterEvents($comment, $args);
                });
            },

            function ($data) use ($args) {
                return $this->loadComment($data)->then(function($comment) use($args) {
                    return $this->createCommentPayload($comment, $args);
                });
            }
        );
    }

    protected function loadComment($data)
    {
        return $this->commentLoader->load($data["commentId"]);
    }

    protected function createCommentPayload(Comment $comment, array $args)
    {
        return [
            "node" => $comment,
            "user" => $comment->userId,
            "photo" => $comment->photoId,
            "edge" => [
                "cursor" => "tmp-cursor", // TODO: use real cursor
                "node" => $comment
            ]
        ];
    }

    protected function filterEvents(Comment $comment, array $args)
    {
        /**
         * @var $photoIdObject IDObject
         */
        $photoIdObject = $args["photoId"];

        return $comment->entityType == Comment::ENTITY_TYPE_PHOTO && $photoIdObject->getId() == $comment->entityId;
    }
}
