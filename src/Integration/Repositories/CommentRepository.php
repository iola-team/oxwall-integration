<?php
namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\CommentRepositoryInterface;
use Everywhere\Api\Entities\Comment;

class CommentRepository implements CommentRepositoryInterface
{
    protected $entityTypeAlias = [
        "photo_comments" => Comment::ENTITY_TYPE_PHOTO,
    ];

    /**
     * @var \BOL_CommentService
     */
    protected $commentService;

    /**
     * @var \BOL_CommentEntityDao
     */
    protected $commentEntityDao;

    public function __construct()
    {
        $this->commentService = \BOL_CommentService::getInstance();
        $this->commentEntityDao = \BOL_CommentEntityDao::getInstance();
    }

    public function findByIds($ids)
    {
        $items = $this->commentService->findCommentListByIds($ids);
        $out = [];
        $entityIds = [];
        foreach ($items as $item) {
            $comment = new Comment();
            $comment->id = (int) $item->id;
            $comment->text = $item->message;
            $comment->createdAt = new \DateTime("@" . $item->createStamp);
            $comment->userId = (int) $item->userId;
            $comment->entityId = (int) $item->commentEntityId; // entityId from related table (ow_base_comment_entity)

            $out[$comment->id] = $comment;

            if (!in_array($item->commentEntityId, $entityIds)) {
                $entityIds[] = $item->commentEntityId;
            }
        }

        $entities = $this->commentEntityDao->findByIdList($entityIds);
        $entitiesMap= [];
        foreach ($entities as $entity) {
            $entitiesMap[$entity->id] = $entity;
        }

        foreach ($out as $comment) {
            $entity = $entitiesMap[$comment->entityId];
            $comment->entityId = $entity->entityId; // id from related table (ow_photo, ow_video_clip and etc.)
            $comment->entityType = $this->entityTypeAlias[$entity->entityType];
        }

        return $out;
    }
}
