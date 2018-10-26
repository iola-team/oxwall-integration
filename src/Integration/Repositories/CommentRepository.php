<?php
namespace Everywhere\Oxwall\Integration\Repositories;

use Everywhere\Api\Contract\Integration\CommentRepositoryInterface;
use Everywhere\Api\Entities\Comment;

class CommentRepository implements CommentRepositoryInterface
{
    public function findByIds($ids)
    {
        $items = \BOL_CommentService::getInstance()->findCommentListByIds($ids);
        $out = [];

        foreach ($items as $item) {
            $comment = new Comment();
            $comment->id = (int) $item->id;
            $comment->text = $item->message;

            $out[$comment->id] = $comment;
        }

        return $out;
    }
}
