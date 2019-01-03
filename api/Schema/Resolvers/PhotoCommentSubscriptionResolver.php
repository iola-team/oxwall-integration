<?php

namespace Everywhere\Api\Schema\Resolvers;

use Everywhere\Api\Contract\Integration\PhotoRepositoryInterface;
use Everywhere\Api\Contract\Integration\CommentRepositoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderFactoryInterface;
use Everywhere\Api\Contract\Schema\DataLoaderInterface;
use Everywhere\Api\Contract\Schema\SubscriptionFactoryInterface;
use Everywhere\Api\Entities\Comment;
use Everywhere\Api\Integration\Events\PhotoCommentAddedEvent;
use Everywhere\Api\Schema\IDObject;
use Everywhere\Api\Schema\SubscriptionResolver;

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
                return $this->createSubscription($args, PhotoCommentAddedEvent::EVENT_NAME);
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

            function ($data) use($args) {
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
        return true;

        // @TODO: use it for notifications
        /**
         * @var $userIdObject IDObject
         */
        $userIdObject = empty($args["userId"]) ? null : $args["userId"];

        /**
         * @var $photoIdObject IDObject
         */
        $photoIdObject = empty($args["photoId"]) ? null : $args["photoId"];

        if ($photoIdObject && $comment->photoId == $photoIdObject->getId()) {
            return true;
        }

        if (!$userIdObject) {
            return false;
        }

        $participantIds = $this->photoRepository->findCommentsParticipantIds([$comment->photoId])[$comment->photoId];

        return in_array($userIdObject->getId(), $participantIds);
    }
}
