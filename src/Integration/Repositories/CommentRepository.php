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

    public function __construct()
    {
        $this->commentService = \BOL_CommentService::getInstance();
    }

    public function findByIds($ids)
    {
        $items = $this->commentService->findCommentListByIds($ids);
        $out = [];

        foreach ($items as $item) {
            $comment = new Comment();
            $comment->id = (int) $item->id;
            $comment->text = $item->message;
            $comment->createdAt = new \DateTime("@" . $item->createStamp);
            $comment->userId = (int) $item->userId;

            $out[$comment->id] = $comment;
        }

        return $out;
    }
}
