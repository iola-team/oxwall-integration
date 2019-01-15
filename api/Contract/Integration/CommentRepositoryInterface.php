<?php
namespace Everywhere\Api\Contract\Integration;

interface CommentRepositoryInterface
{
    /**
     * @param array $ids
     * @return array<Comment>
     */
    public function findByIds($ids);

    /**
     * @param integer $id
     * @return \BOL_CommentEntity
     */
    public function findCommentEntityById($id);
}
