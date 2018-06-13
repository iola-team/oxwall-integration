<?php

namespace Everywhere\Api\Contract\Integration;

use Everywhere\Api\Entities\User;

interface ChatRepositoryInterface
{
    /**
     * @param string[] $ids
     *
     * @return User[]
     */
    public function findChatsByIds($ids);
}
