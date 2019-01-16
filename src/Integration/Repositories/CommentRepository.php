<?php
namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\CommentRepositoryInterface;
use Everywhere\Api\Entities\Comment;

class CommentRepository implements CommentRepositoryInterface
{
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
            $comment->entityId = (int) $item->commentEntityId;

            $out[$comment->id] = $comment;

            if (!in_array($item->commentEntityId, $entityIds)) {
                $entityIds[] = $item->commentEntityId;
            }
        }

        // Set photoId for comments
        $entities = $this->commentEntityDao->findByIdList($entityIds);
        $entityIdsToPhotoIds = [];
        foreach ($entities as $entity) {
            $entityIdsToPhotoIds[$entity->id] = (int) $entity->entityId;
        }
        foreach ($out as $comment) {
            $comment->photoId = $entityIdsToPhotoIds[$comment->entityId];
        }

        return $out;
    }
}
