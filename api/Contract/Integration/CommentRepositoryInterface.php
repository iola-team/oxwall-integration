<?php
namespace Everywhere\Api\Contract\Integration;

interface CommentRepositoryInterface
{
    /**
     * @param array $ids
     * @return array<Comment>
     */
    public function findByIds($ids);
}
