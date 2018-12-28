<?php

namespace Everywhere\Api\Schema\Resolvers;

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
     * @var SubscriptionFactoryInterface
     */
    protected $subscriptionFactory;

    /**
     * @var DataLoaderInterface
     */
    protected $commentLoader;

    /**
     * @var CommentRepositoryInterface
     */
    protected $commentRepository;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        DataLoaderFactoryInterface $loaderFactory,
        SubscriptionFactoryInterface $subscriptionFactory
    )
    {
        parent::__construct([
            "onPhotoCommentAdd" => function($root, $args) {
                return $this->createSubscription($args, PhotoCommentAddedEvent::EVENT_NAME);
            },
        ]);

        $this->commentLoader = $loaderFactory->create(function($ids) use($commentRepository) {
            return $commentRepository->findByIds($ids);
        });

        $this->subscriptionFactory = $subscriptionFactory;
        $this->commentRepository = $commentRepository;
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
            "chatEdge" => [
                "cursor" => "tmp-cursor", // TODO: use real cursor
                "node" => $comment->photoId
            ],
            "edge" => [
                "cursor" => "tmp-cursor", // TODO: use real cursor
                "node" => $comment
            ]
        ];
    }

    protected function filterEvents(Comment $comment, array $args)
    {
        // @TODO
//        /**
//         * @var $userIdObject IDObject
//         */
//        $userIdObject = empty($args["userId"]) ? null : $args["userId"];
//
//        /**
//         * @var $photoIdObject IDObject
//         */
//        $photoIdObject = empty($args["photoId"]) ? null : $args["photoId"];
//
//        if ($photoIdObject && $comment->photoId == $photoIdObject->getId()) {
//            return true;
//        }
//
//        if (!$userIdObject) {
//            return false;
//        }
//
//        $participantIds = $this->photoRepository->findPhotoParticipantIds([$comment->photoId])[$comment->photoId];
//
//        return in_array($userIdObject->getId(), $participantIds);
        return true;
    }
}
