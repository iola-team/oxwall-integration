<?php

namespace Iola\Api\Contract\Integration;

interface CommentRepositoryInterface
{
    /**
     * @param array $ids
     * @return array<Comment>
     */
    public function findByIds($ids);
}
